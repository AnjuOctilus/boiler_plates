<?php


namespace App\Repositories\DataIngestion;

use App\Jobs\GenerateLOAPDF;
use App\Models\FollowupVisit;
use App\Models\User;
use App\Models\UserMilestoneStats;
use App\Repositories\Interfaces\FollowupDataIngestionInterface;
use App\Repositories\Interfaces\S3SignatureDataIngestionInterface;
use App\Repositories\LogRepository;
use App\Repositories\PixelFireRepository;
use App\Repositories\VisitorRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Repositories\Interfaces\LiveSessionInterface;
use App\Repositories\Interfaces\PixelFireInterface;
use App\Repositories\Interfaces\SignatureDataIngestionInterface;
use App\Repositories\Interfaces\HistoryInterface;
use Illuminate\Support\Facades\Log;
use App\Models\Signature;
use App\Models\UserExtraDetail;
use App\Models\UserQuestionnaireAnswers;
use App\Repositories\HistoryRepository;
use App\Repositories\Interfaces\QuestionnairesInterface;
use App\Models\FollowupHistories;
use App\Repositories\Interfaces\PDFGenerationInterface;
use App\Jobs\PostLeadsToCake;
use App\Jobs\GeneratePdf;
use App\Repositories\UserRepository;

/**
 * Class FollowupDataIngestionRepository
 *
 * @package App\Repositories\DataIngestion
 */
class FollowupDataIngestionRepository implements FollowupDataIngestionInterface
{
    /**
     * FollowupDataIngestionRepository constructor.
     *
     * @param LiveSessionInterface $liveSessionInterface
     * @param PixelFireInterface $pixelFireInterface
     * @param HistoryInterface $historyInterface
     * @param S3SignatureDataIngestionInterface $s3SignatureDataIngestionInterface
     * @param PDFGenerationInterface $pdf_generation_repo
     */
    public function __construct(
    LiveSessionInterface $liveSessionInterface,PixelFireInterface $pixelFireInterface,
    HistoryInterface $historyInterface,
    S3SignatureDataIngestionInterface $s3SignatureDataIngestionInterface,
    QuestionnairesInterface $questionnairesInterface, PDFGenerationInterface $pdf_generation_repo)
    {
        $this->liveSessionInterface = $liveSessionInterface;
        $this->pixelFireInterface = $pixelFireInterface;
        $this->historyInterface = $historyInterface;
        $this->S3SignatureDataIngestionInterface = $s3SignatureDataIngestionInterface;
        $this->questionnairesInterface = $questionnairesInterface;
        $this->pdf_generation_repo = $pdf_generation_repo;
    }
    /**
     * Save follow up load data
     *
     * @param $data
     * @param $visitorParameters
     * @param $page
     */
    public function saveFollowUpData($data,$queryString,$currentTime)
    {
        $request = $data;
        $type = (isset($data['type']) && !empty($data['type'])) ? $data['type']: '';
        $data = $data['followup_data'];
        $token = isset($data['atp_sub2']) ? $data['atp_sub2'] : null;
        $logRepoObject = new LogRepository();
        if ($token) {
            $visitorData = $this->getVisitorDetails($data);
            $userDetails = $this->getUserDetails($token);
            if (isset($visitorData->id) && !empty($visitorData->id)) {
                $flvvisit_id = $visitorData->id;
            } else {
                $queryStringData = array();
                parse_str($queryString, $queryStringData);
                if (isset($userDetails->id) && !empty($userDetails->id)) {
                    $followupVisitObj = new FollowupVisit();
                    $followupVisitObj->user_id = $userDetails->id;
                    $followupVisitObj->visitor_id = $userDetails->visitor_id;
                    $followupVisitObj->tracker_unique_id = isset($data['pixel']) ? $data['pixel'] : '';
                    $followupVisitObj->atp_sub2 = isset($data['atp_sub2']) ? $data['atp_sub2'] : '';
                    $followupVisitObj->source = isset($data['atp_sub6']) ? $data['atp_sub6'] : '';
                    $followupVisitObj->type =  $type;
                    $followupVisitObj->request = serialize($queryStringData);
                    $followupVisitObj->save();
                    $flvvisit_id = $followupVisitObj->id;
                } else {
                    $logRepoObject->writeLog('_Followup_user_not_exist', [$data['atp_sub2']]);
                    return false;
                }
            }
            $pixelFireRepoObject = new PixelFireRepository();
            $flagFLPVisit = $pixelFireRepoObject->getAdvPixelFireStatus("FLP", $userDetails->vistor_id);
            if (!$flagFLPVisit) {
                $pixelFireRepoObject->setPixelFireStatus("FLP", $userDetails->vistor_id, $userDetails->id);
            }
            $flagFLPVisitFollowup = $pixelFireRepoObject->getFollowupPixelFireStatus("LP", $flvvisit_id);
            $visitorRepoObject = new VisitorRepository();
            $arrParam['tracker'] = isset($data['tracker']) ? $data['tracker'] : "";
            $arrParam['pixel'] = isset($data['pixel']) ? $data['pixel'] : "";
            $arrParam['atp_source'] = isset($data['atp_source']) ? $data['atp_source'] : "";
            $arrParam['atp_vendor'] = isset($data['atp_vendor']) ? $data['atp_vendor'] : "";
            $arrParam['atp_sub1'] = isset($data['atp_sub1']) ? $data['atp_sub1'] : "";
            $arrParam['atp_sub2'] = isset($data['atp_sub2']) ? $data['atp_sub2'] : "";
            $arrParam['atp_sub3'] = isset($data['atp_sub3']) ? $data['atp_sub3'] : "";
            $arrParam['atp_sub4'] = isset($data['atp_sub4']) ? $data['atp_sub4'] : "";
            $arrParam['url_id'] = isset($data['url_id']) ? $data['url_id'] : "";
            $arrParam['lp_id'] = isset($data['lp_id']) ? $data['lp_id'] : "";
            $tracker_type = $visitorRepoObject->defineTrackerType($arrParam);
            $currentUrl = URL::full();
            if (!$flagFLPVisitFollowup) {
                if ($tracker_type == 1) {
                    $chkArry = array("tracker_type" => $tracker_type,
                        "tracker" => isset($data['tracker']) ? $data['tracker'] : null,
                        "atp_vendor" => isset($data['atp_vendor']) ? $data['atp_vendor'] : null,
                        "pixel" => $data['pixel'],
                        "pixel_type" => "LP",
                        "statusupdate" => "SPLIT",
                        "intVisitorId" => $userDetails->visitor_id,
                        "redirecturl" => $currentUrl,
                        'flvvisit_id' => $flvvisit_id,
                        'user_id' => $userDetails->id,
                        'currentTime' => @$currentTime
                    );
                    $arrResultDetail = $pixelFireRepoObject->atpFollowupPixelFire($chkArry);
                    if (!empty($arrResultDetail)) {
                        $resp = serialize($arrResultDetail);
                        if (@$flvvisit_id != "") {
                            FollowupVisit::where(['id' => $flvvisit_id])->update(['fireflag' => 1, 'adtopia_response' => @$resp]);
                        }
                    } else {
                        $resp = '';
                    }
                    $logRepoObject->writeLog('-FL-LPfire', "array : " . serialize($chkArry) . " response:" . serialize($arrResultDetail));
                }
            }
        } else {
            //@todo write to  log
            $logRepoObject->writeLog('Followup_user_not_exist', [$data['atp_sub2']]);
        }
    }
    /**
     * Get user details
     *
     * @param $token
     * @return mixed
     */
    public function getUserDetails($token)
    {
        $user = User::where(['token' => $token])->first();
        return $user;
    }
    /**
     * Get visitor details
     *
     * @param $data
     * @return mixed
     */
    public function getVisitorDetails($data)
    {
        $result = FollowupVisit::where(['atp_sub2' => $data['atp_sub2'], 'tracker_unique_id' => $data['pixel']])->first();
        return $result;
    }
    /**
     *store Flp signature data
     *
     * @param $signatureData,$followupData,$previousData,
     * @return mixed
     */
    public function signatureStore($signatureData, $currentTime,$followupdata)
    {
       
        $token = $followupdata['atp_sub2'];
        $user =  $this->getUserDetails($token);
        if( $user ){
            $visitorId =  $user->visitor_id;
            $userId = $user->id;
        }
        $s3_signatureData = $this->S3SignatureDataIngestionInterface->userS3SignatureStore($signatureData,$userId,'user');
        $type                       = 'digital';
        $signatureResult            = Signature::where('user_id', '=', $userId)
                                        ->first();

        if (!empty($signatureResult)) {
            $signatureResult->s3_file_path   = $s3_signatureData;
            $signatureResult->status            = 1;
            $signatureResult->type              = $type;
            $signatureResult->update();
            $signature_id                          = $signatureResult->id;
        } else {
            $objSignature                       = new Signature;
            $objSignature->user_id              = $userId;
            $objSignature->bank_id              = 0;
            $objSignature->s3_file_path     = $s3_signatureData;
            $objSignature->status               = 1;
            $objSignature->type                 = $type;
            $objSignature->save();
            $signature_id                          = $objSignature->id;
        }

        $this->updateTYPixel($followupdata,$currentTime);
        $source =  isset($followupData['atp_sub6'])? $followupData['atp_sub6'] : 'FLP';

        if ($signature_id) {
            $this->historyInterface->insertFollowupLiveHistory(array(
                        "user_id" =>$userId,
                        "type" =>'signature',
                        "type_id" =>0,
                        "source" =>'FLP',
                        "value" =>'1',
                        "post_crm" =>0,
                    )
                );
            $this->liveSessionInterface->createUserMilestoneStats(array(
                    "user_id" => $userId,
                    "source" => $source,
                    "user_signature" => 1,
                )
            );
            $this->pixelFireInterface->SetPixelFireStatus("FLSN", $visitorId, $userId);
            self::updateFollowUpCompleteStatus($userId,$source);

           

        }
        return;
    }
    /**
     * Update TY Pixel
     *
     * @param $data
     */
    public function updateTYPixel($data,$currentTime)
    {
        $token = isset($data['atp_sub2']) ? $data['atp_sub2'] : null;
        $pixelFireRepoObject = new PixelFireRepository();
        $visitorData = $this->getVisitorDetails($data);
        $userDetails = $this->getUserDetails($token);
        $flvvisit_id = $visitorData->id;
        $flagFLPVisitFollowup = $pixelFireRepoObject->getFollowupPixelFireStatus("TY", $flvvisit_id);
        $visitorRepoObject = new VisitorRepository();
        $arrParam['tracker'] = isset($data['tracker']) ? $data['tracker'] : "";
        $arrParam['pixel'] = isset($data['pixel']) ? $data['pixel'] : "";
        $arrParam['atp_source'] = isset($data['atp_source']) ? $data['atp_source'] : "";
        $arrParam['atp_vendor'] = isset($data['atp_vendor']) ? $data['atp_vendor'] : "";
        $arrParam['atp_sub1'] = isset($data['atp_sub1']) ? $data['atp_sub1'] : "";
        $arrParam['atp_sub2'] = isset($data['atp_sub2']) ? $data['atp_sub2'] : "";
        $arrParam['atp_sub3'] = isset($data['atp_sub3']) ? $data['atp_sub3'] : "";
        $arrParam['atp_sub4'] = isset($data['atp_sub4']) ? $data['atp_sub4'] : "";
        $arrParam['url_id'] = isset($data['url_id']) ? $data['url_id'] : "";
        $arrParam['lp_id'] = isset($data['lp_id']) ? $data['lp_id'] : "";
        $tracker_type = $visitorRepoObject->defineTrackerType($arrParam);
        $currentUrl = URL::full();
        if (!$flagFLPVisitFollowup) {
            if ($tracker_type == 1) {
                $chkArry = array("tracker_type" => $tracker_type,
                    "tracker" => isset($data['tracker']) ? $data['tracker'] : null,
                    "atp_vendor" => isset($data['atp_vendor']) ? $data['atp_vendor'] : null,
                    "pixel" => $data['pixel'],
                    "pixel_type" => "TY",
                    "statusupdate" => "SPLIT",
                    "intVisitorId" => $userDetails->visitor_id,
                    "redirecturl" => $currentUrl,
                    'flvvisit_id' => $flvvisit_id,
                    'user_id' => $userDetails->id,
                    'currentTime' => @$currentTime
                );
                $arrResultDetail = $pixelFireRepoObject->atpFollowupPixelFire($chkArry);
            }
        }
    }
    public function updateFollowUpCompleteStatus($userId,$source)
    {
        $signData = self::getSignData($userId);
        $questionData = self::getQuestionData($userId);
        $time_now = Carbon::now();
        // $update = UserMilestoneStats::where(['user_id' => $userId] )
        //                             ->where(['source'=> $source]);
                           
        if (!empty($signData->user_sign) && !empty($questionData)) {

            UserMilestoneStats::where(['user_id' => $userId] )
                                ->where(['source'=> $source])
                                ->update(
                                [
                                    'user_completed' => 1,
                                    'user_completed_date' => $time_now,
                                    'completed' => 1,
                                    'completed_date' => $time_now
                                ]
                );

            UserExtraDetail::where('user_id', $userId)->update(['complete_status' => 1]);
        }
    }
    public function getQuestionData($userId)
    {
        $questions = $this->totalQuestionArray();
        $answerCount = DB::table('questionnaires AS Q')
            ->leftJoin('user_questionnaire_answers AS UQA', 'Q.id', '=', 'questionnaire_id')
            ->where('UQA.user_id', '=', $userId)
            ->select('UQA.questionnaire_id')
            ->whereIn('UQA.questionnaire_id', ['5','6','7','8','9','10','11','12','13','14'])
            ->groupBy('UQA.questionnaire_id')
            ->get()
            ->toArray();
        
        if (sizeof($answerCount) > 9) {
            return 1;
        } else {
            return 0;
        }
    }
    public function getSignData($userId)
    {
        $result = User::where(['users.id' => $userId])
            ->leftJoin('signatures', 'users.id', 'signatures.user_id')
            ->select('signatures.s3_file_path as user_sign')
            ->first();
        return $result;
    }

    public function questionStore($questionData,$followupdata){
        $token = isset($followupdata['atp_sub2']) ? $followupdata['atp_sub2'] : 'null';
        $userRepository = new UserRepository();
        $user =  $this->getUserDetails($token);
        if( $user ){
            $visitorId =  $user->visitor_id;
            $userId = $user->id;
        }
        $type = 'questionaire1';
        $source =  isset($followupData['atp_sub6'])? $followupData['atp_sub6'] : 'FLP';
        $qId = isset($questionData['question_id']) ? $questionData['question_id'] : null;
  
        $optionId = isset($questionData['option_id']) ? $questionData['option_id'] : null;
        $inputAnswer = isset($questionData['input_answer']) ? $questionData['input_answer'] : null;
        $type = 'questionaire1';
        $source =  isset($followupData['atp_sub6'])? $followupData['atp_sub6'] : 'FLP';
        if ($userId) {
            if($qId == 1){
                UserQuestionnaireAnswers::Create(
                    [
                        'user_id' => $userId,
                        'questionnaire_id' => $qId,
                        'questionnaire_option_id' => $optionId,
                        'input_answer' => $inputAnswer

                    ]
                );
                FollowupHistories::Create(
                    [
                        'user_id' => $userId,
                        'type' => $type,
                        'type_id' => isset($qId)?$qId:0,
                        'value' => ($optionId == 27 || $optionId == 32) ? $inputAnswer : $optionId,
                        'source' => $source
                    ]
                );
                $questionStatus = $this->isQuestionnaireComplete($userId);
                if($questionStatus){
                    $this->liveSessionInterface->createUserMilestoneStats(array(
                            "user_id" => $userId,
                            "source" => $source,
                            "questions" => 1,
                        )
                    );
                }
        }else{
                UserQuestionnaireAnswers::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'questionnaire_id' => $qId,

                    ],
                    [
                        'user_id' => $userId,
                        'questionnaire_option_id' => $optionId,
                        'input_answer' => $inputAnswer

                    ]
                );
                if($qId == 15){
                      $historyData = [
                        'user_id' => $userId,
                        'type' => $type,
                        'type_id' => isset($qId)?$qId:0,
                        'value' => $inputAnswer,
                        'source' => $source
                        ];
                }else{
                        $historyData = [
                        'user_id' => $userId,
                        'type' => $type,
                        'type_id' => isset($qId)?$qId:0,
                        'value' => $optionId,
                        'source' => $source
                        ];
                }
            $followUpHistory = new HistoryRepository();
            $followUpHistory->insertFollowupLiveHistory($historyData);
            $questionStatus = $this->isQuestionnaireComplete($userId);
            if($questionStatus){
                $this->liveSessionInterface->createUserMilestoneStats(array(
                        "user_id" => $userId,
                        "source" => $source,
                        "questions" => 1,
                    )
                );
            }
        }

                $followUpHistory = new HistoryRepository();
                $followUpHistory->insertFollowupLiveHistory($historyData);
                $questionStatus = $this->questionnairesInterface->isQuestionnaireComplete($userId);
                $userFilledQuestionCount = $this->getFollowUpPendingQuestionsCount($userId);
                $userBanks = $userRepository->getUserDetailsFromUserId($userId);
                echo "==================$userFilledQuestionCount.=================".$userFilledQuestionCount;echo "\n";
           //Execute necessary queue jobs after completed questionnaires
                $i=0;
                if($userFilledQuestionCount > 9 ){
                    foreach($userBanks as $key=>$userBank){
                        $userBankName =$userBank['bank_name'];
                        $userBankId = $userBank['bank_id']; 
                        $i++;
                        $count = $i;
                       dispatch(new GenerateLOAPDF($userId,$key,$userBankName,$userBankId,$count));
                    }
                    
                    $this->liveSessionInterface->completedStatusUpdate($userId, 'live');
                    self::updateFollowUpCompleteStatus($userId,$source);
                    UserExtraDetail::where('user_id', $userId)->update(['complete_status' => 1]);
                    dispatch(new GeneratePdf($userId));
                    $userRecords = User::where('id',$userId)->first();
                    $userMileStoneRecords  = UserMilestoneStats::where('user_id',$userId)->first();
                    $recordStatus       = isset($userRecords->record_status) ? $userRecords->record_status : 'TEST';
                    $APP_ENV = env('APP_ENV');
                    if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
                        $milestone_status   = isset($userMileStoneRecords->source) ? $userMileStoneRecords->source : 'live';
                    }
                    else{
                       $milestone_status = 'TEST';
                    }
                    
                   // 
                      if(!empty($userId)){
                        dispatch(new PostLeadsToCake($userId, $recordStatus, $milestone_status));
                    }

                }
                if($questionStatus){
                    /*$this->liveSessionInterface->createUserMilestoneStats(array(
                            "user_id" => $userId,
                            "source" => $source,
                            "questions" => 1,
                        )
                    );*/
                }
           // }
           //self::updateFollowUpCompleteStatus($userId,$source);
        }
    }

    //Return count for pending question from constants
    public function getFollowUpPendingQuestionsCount($user_id)
    {
        $data = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')
            ->where('uqa.user_id', $user_id)
            ->whereNotIn('uqa.questionnaire_id', [1, 15])
            ->select('uqa.questionnaire_id')
            ->get()->count();
        
        return $data;
    }

    public function totalQuestionArray(){
        $questions = \Illuminate\Support\Facades\DB::table('questionnaires') 
        ->where('questionnaires.id', '>=', 5)    
        ->where('questionnaires.id', '<=', 14 )  
        ->select('questionnaires.id')->get()->toArray();   
        return $questions;
    }


/**
     * Get total question count
     */
    public function getTotalQuestionCount(){
        $questionCount = \Illuminate\Support\Facades\DB::table('questionnaires')
        ->where('questionnaires.id', '>=', 5)    
        ->where('questionnaires.id', '<=', 14 )
            ->select('questionnaires.id')
            ->get()->count();
            return $questionCount;
    }
    /**
     * Return dynamic questions array
     */
    public function getQuestionArray(){
        $questions = $this->totalQuestionArray();
        $questions = array_column($questions, 'id');
        //$questionArray = implode(',',$questions);
        //$questionArray = '['.$questionArray.']';
        //echo $questionArray;
        return $questions;
    }

    public function isQuestionnaireComplete($userId)
    {
           $questionArray = $this->getQuestionArray();
           $questionCount = $this->getTotalQuestionCount();
           $answerCount = DB::table('questionnaires AS Q')
            ->leftJoin('user_questionnaire_answers AS UQA','Q.id','=','questionnaire_id')
            ->where('UQA.user_id','=',$userId)
            ->select('UQA.questionnaire_id','UQA.questionnaire_option_id')
            ->whereIn('UQA.questionnaire_id',$questionArray)
            ->groupBy('UQA.questionnaire_id')
            ->get()
            ->toArray();
        if (sizeof($answerCount) >= $questionCount) {
            return 1;
        } else {
            return 0;
        }
    }




}
