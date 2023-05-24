<?php

namespace App\Repositories;


use App\Models\SplitUuid;
use App\Models\User;
use App\Repositories\Interfaces\BuyerApiInterface;
use App\Repositories\Interfaces\HistoryInterface;
use App\Repositories\Interfaces\LiveSessionInterface;
use App\Repositories\Interfaces\LogInterface;
use App\Repositories\Interfaces\PDFGenerationInterface;
use App\Repositories\Interfaces\LPDataIngestionInterface;
use App\Repositories\Interfaces\PixelFireInterface;
use App\Repositories\Interfaces\VisitorInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Questionnaire;
use App\Repositories\UserRepository;
use App\Repositories\CakeRepository;
use DB;
use App\Repositories\Interfaces\QuestionnairesInterface;
use App\Jobs\PDFGeneration;
use App\Models\UserQuestionnaireAnswers;
use App\Models\FollowupHistories;
use App\Models\UserExtraDetail;
use App\Jobs\PostLeadsToCake;
use App\Models\UserMilestoneStats;
use App\Jobs\GeneratePdf;
use App\Jobs\GenerateLOAPDF;

/**
 * Class QuestionnairesRepository
 *
 * @package App\Repositories
 */
class QuestionnairesRepository implements QuestionnairesInterface
{
    /***
     * QuestionnairesRepository constructor.
     *
     * @param VisitorInterface $visitorInterface
     * @param LPDataIngestionInterface $LPDataIngestionInterface
     * @param LiveSessionInterface $liveSessionInterface
     * @param PixelFireInterface $pixelFireInterface
     * @param HistoryInterface $historyInterface
     * @param LogInterface $logInterface
     * @param BuyerApiInterface $buyerApiInterface
     * @param PDFGenerationInterface $pdf_generation_repo
     */
    public function __construct(
        VisitorInterface $visitorInterface,
        LPDataIngestionInterface $LPDataIngestionInterface,
        LiveSessionInterface $liveSessionInterface,
        PixelFireInterface $pixelFireInterface,
        HistoryInterface $historyInterface,
        LogInterface $logInterface,
        BuyerApiInterface $buyerApiInterface,
        PDFGenerationInterface $pdf_generation_repo
    ) {
        $this->visitorInterface = $visitorInterface;
        $this->LPDataIngestionInterface = $LPDataIngestionInterface;
        $this->liveSessionInterface = $liveSessionInterface;
        $this->pixelFireInterface = $pixelFireInterface;
        $this->historyInterface = $historyInterface;
        $this->logInterface = $logInterface;
        $this->buyerApiInterface = $buyerApiInterface;
        $this->cakeRepo = new CakeRepository();
        $this->userRepository = new userRepository();
        $this->pdf_generation_repo = $pdf_generation_repo;
   
    }

    /**
     * Is quesitonaire complete
     *
     * @param $userId
     * @return int
     */
    public function isQuestionnaireComplete($userId)
    {
           $questionArray = $this->getQuestionArray();
           $questionCount = $this->getTotalQuestionCount();
           $answerCount = DB::table('questionnaires AS Q')
            ->leftJoin('user_questionnaire_answers AS UQA','Q.id','=','questionnaire_id')
            ->where('UQA.user_id','=',$userId)
            ->select('UQA.questionnaire_id','UQA.questionnaire_option_id')
           //->whereIn('UQA.questionnaire_id',[5,6,7,8,9,10,11,12])
            ->whereIn('UQA.questionnaire_id',$questionArray)
            ->groupBy('UQA.questionnaire_id')
            ->get()
            ->toArray();

        //$questionCount = 8;
        // foreach ($answerCount as $key => $value) {
        //     if($value->questionnaire_id == '9' && $value->questionnaire_option_id == '18'){
        //         $questionCount = 7;
        //         break;
        //     }

        // }
        if (sizeof($answerCount) >= $questionCount) {
            return 1;
        } else {
            return 0;
        }
    }

    public function totalQuestionArray(){
        $questions = \Illuminate\Support\Facades\DB::table('questionnaires') 
        ->where('questionnaires.id', '>=', 5)    
        ->where('questionnaires.id', '<=', 14 )  
        ->select('questionnaires.id')->get()->toArray();   
        return $questions;
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
    /**
     * Get total question count
     */
    public function getTotalQuestionCount(){
        $questionCount = \Illuminate\Support\Facades\DB::table('questionnaires')
        ->where('questionnaires.id', '>=', 5)    
        ->where('questionnaires.id', '<=', 14 )
        //->where(['questionnaires.id', '>=', 5,'questionnaires.id', '<=', 14])  
            //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
            //->where('questionnaires.user_id', $user_id)
            //->whereNotIn('uqa.questionnaire_id', [1, 15])
            //->whereIn('que.id', [1,2,3,4,5,6,7,8,9,10])
            ->select('questionnaires.id')
            ->get()->count();
            return $questionCount;
    }
    /**
     * Create common Question array
     */
    public function getCommonQuestionArray(){
            $questionCount = $this->getTotalQuestionCount();
            $startIndex = 5;
            $endIndex = $startIndex + $questionCount;
            $commonArray = [];
            
            for($i=$startIndex; $i < $endIndex; $i++){
                array_push($commonArray,$i);
            }
            return $commonArray;
    }

    public function isPartnerQuestionnaireComplete($userId)
    {
        //
    }

    public function isQuestionnairePageComplete($userId)
    {
        //
    }

    public function createUserQuestionnaireStats($request)
    {
        //
    }

    public function getQuestionnaireVersion()
    {
        //
    }

    /**
     * Get random
     *
     * @param $url_array
     * @param $numerical_array
     * @return mixed
     */
    public function getRandom($url_array, $numerical_array)
    {
        $winner = (mt_rand(1, 100));
        $inital_value = 0;
        $final_value = 0;
        foreach ($url_array as $key => $value) {
            $final_value = $final_value + $value['config_value'];
            $output = array_slice($numerical_array, $inital_value, $final_value, true);
            foreach ($output as $key1 => $value1) {
                $numerical_array[$key1]['config_title'] = $value['config_title'];
                $numerical_array[$key1]['config_value'] = $value['config_value'];
            }
            $inital_value = $final_value;
        }
        return $numerical_array[$winner];
    }

    public function createquestionnaireMeta($request)
    {
        //
    }

    public function checkQuestionnaireVersion($userId)
    {
        //
    }

    /**
     * Get pending question
     *
     * @param $user_id
     * @return array
     */
    public function getPendingQuestions($user_id)
    {
        $questionArray = $this->getQuestionArray();
        $data = \Illuminate\Support\Facades\DB::table('questionnaires as que')
            ->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
            ->where('uqa.user_id', $user_id)
           //->whereIn('que.id', [5,6,7,8,9,10,11,12])
           ->whereIn('que.id', $questionArray)
            ->select('que.id')
            ->get()->toArray();
        $result = [];
        $questions = $questionArray;
        foreach ($data as $each){
                 array_push($result,$each->id);
        }
        $result = array_diff($questions,$result);
        // $del_qn = 11;
        // if (($key = array_search($del_qn, $result)) !== false) {
        //     unset($result[$key]);
        // }
        return array_values($result);
    }

    public function getFilledQuestions($user_id)
    {
        $data = \Illuminate\Support\Facades\DB::table('questionnaires as que')
            ->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
            ->where('uqa.user_id', $user_id)
            ->select('que.id')
            ->get()->toArray();
        $result = [];
         foreach ($data as $each){
                 array_push($result,$each->id);
         }
        return array_values($result);
    }

    /**
     * Save questionaires
     *
     * @param $visitorParamter
     * @param $data
     */
    public function saveQuestionaires($visitorParamter, $questionData, $formData, $visitorData, $queryString)
    {
       $uuId = $visitorParamter['uuid'];
       $userRepository = new UserRepository();
        $userId = $this->getUser($uuId,$formData,$visitorData,$queryString,$visitorParamter);
        $qId = isset($questionData['question_id']) ? $questionData['question_id'] : null;
        $optionId = isset($questionData['option_id']) ? $questionData['option_id'] : null;
        $inputAnswer = isset($questionData['input_answer']) ? $questionData['input_answer'] : null;
        $type = 'questionaire1';
        $source = 'live';
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
            $userBanks = $userRepository->getUserDetailsFromUserId($userId);
            $questionCount  = $this->getFollowUpPendingQuestionsCount($userId);
            if($questionCount > 9){
            //Loa PDF Generaion
            $i=0;
            foreach($userBanks as $key=>$userBank){
                $userBankName =$userBank['bank_name'];
                $userBankId = $userBank['bank_id']; 
                $i++;
                $count = $i;
               dispatch(new GenerateLOAPDF($userId,$key,$userBankName,$userBankId,$count));
               
            }
            $this->liveSessionInterface->completedStatusUpdate($userId, 'live');
            // dd($userId);
            UserExtraDetail::where('user_id', $userId)->update(['complete_status' => 1]);
            //COA,Review,statement,Questionnaire PDF Generation
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
        }
    }

    //  check the is_qulified condition
    public function checkUserQulificationStatus($user_id)
    {
        //
    }
    /**
     * Get user
     *
     * @param $uuid
     * @return null
     */
    public function getUser($uuid, $formData, $visitorData, $queryString, $visitorParamter)
    {
        $visitor = SplitUuid::where(['uuid' => $uuid])->first();
        if (isset($visitor->visitor_id) && !empty($visitor->visitor_id)) {
            $user = (isset($visitor->visitor_id)) ? User::where(['visitor_id' => $visitor->visitor_id])->first() : null;
        } else {
            $currentTime = Carbon::now()->format('Y-m-d H:i:s');
            $data['currentTime'] = $currentTime;
            $arrResponse = $this->LPDataIngestionInterface->store($formData, $queryString, $visitorParamter, $currentTime, $formData['page_name'], $visitorData);
            $visitor = SplitUuid::where(['uuid' => $uuid])->first();
            $user = (isset($visitor->visitor_id)) ? User::where(['visitor_id' => $visitor->visitor_id])->first() : null;
        }
        return (isset($user->id)) ? $user->id : null;
    }

    public function createUserMileStone()
    {
        //
    }


    public function getFollowUpPendingQuestions($user_id)
    {

        $data = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')
            //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
            ->where('uqa.user_id', $user_id)
            //->whereIn('que.id', [1,2,3,4,5,6,7,8,9,10])
            ->select('uqa.questionnaire_id')
            ->get()->toArray();

        $result = [];
        $commonArray = $this->getCommonQuestionArray();
        $questions = $this->getCommonQuestionArray();
        //$questions = [1,2,3,4,5,6,7,8,9,10,11];

        foreach ($data as $each){
            array_push($result,$each->questionnaire_id);
        }

        $result = array_diff($questions,$result);
        // $del_qn = 11;
        // if (($key = array_search($del_qn, $result)) !== false) {
        //     unset($result[$key]);
        // }
        return array_values($result);
    }
    //Return count for pending question from constants
    public function getFollowUpPendingQuestionsCount($user_id)
    {

        $data = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')
            //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
            ->where('uqa.user_id', $user_id)
            ->whereNotIn('uqa.questionnaire_id', [1, 15])
            //->whereIn('que.id', [1,2,3,4,5,6,7,8,9,10])
            ->select('uqa.questionnaire_id')
            ->get()->count();
        
        return $data;
    }

    //get FilledUpquestion

    public function getFollowUpFilledUpQuestions($user_id){
        $data = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')
        //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
        ->where('uqa.user_id', $user_id)
        //->whereIn('que.id', [1,2,3,4,5,6,7,8,9,10])
        ->select('uqa.questionnaire_id')
        ->get()->toArray();
        $result = [];
        foreach ($data as $each){
            array_push($result,$each->questionnaire_id);
        }
        return $result;
    }
    /**
     * Return Answers for followupquestions
     */
    public function getFollowUpQuestionsAnswers($user_id){
        $data = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')
        //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
        ->where('uqa.user_id', $user_id)
        ->whereNotIn('uqa.questionnaire_id', [1, 15])
        //->whereIn('que.id', [1,2,3,4,5,6,7,8,9,10])
        ->select('uqa.questionnaire_option_id')
        ->get()->toArray();
        $result = [];
        foreach ($data as $each){
            array_push($result,$each->questionnaire_option_id);
        }
        return $result;
    }

    /*public function getFollowUpQuestionsAnswers($user_id){
        $data = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')
        //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
        ->where('uqa.user_id', $user_id)
        //->whereIn('que.id', [1,2,3,4,5,6,7,8,9,10])
        ->select('uqa.questionnaire_id','uqa.questionnaire_option_id')
        ->get()->toArray();
        $result = [];
        $questions = [];

        foreach ($data as $key=> $each){
           $questions[$key] = $each->questionnaire_option_id;
           array_push($result, $questions );
         
            
        }
       
        return $result;

    }*/

   

   
        


}
