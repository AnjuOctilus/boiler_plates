<?php


namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Jobs\GenerateLOAPDF;
use App\Models\SplitUuid;
use App\Models\UnsubscribeUser;
use App\Models\UserExtraDetail;
use App\Models\SignatureDetails;
use App\Models\User;
use App\Models\VisitorUnqualified;
use App\Repositories\FollowupRepository;
use App\Repositories\Interfaces\ApiClassInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\S3SignatureDataIngestionInterface;
use App\Models\UserQuestionnaireAnswers;
use App\Repositories\Interfaces\UserInterface;
use App\Jobs\SendSMSJob;
use App\Jobs\UserInsertion;
use App\Jobs\SaveSignature;
use App\Jobs\SaveQuestion;
use DB;
use App\Jobs\GeneratePdf;
use App\Jobs\PostLeadsToCake;
use App\Jobs\RegenerateLoaPDFApi;
use App\Jobs\GenerateLOApdfApi;
use App\Jobs\GeneratePdfApi;
use App\Jobs\RegeneratePdf;
use App\Jobs\RestoreSignature;
use App\Models\LeadDoc;
use App\Models\LeadDocBase;
use App\Models\UserMilestoneStats;
use App\Repositories\UserRepository;
use Aws;

class UserController extends Controller
{
    /**
     * Constructor
     *
     * @param ApiClassInterface $apiClassInterface
     */
    public function __construct(ApiClassInterface $apiClassInterface, S3SignatureDataIngestionInterface $S3SignatureDataIngestionInterface,UserInterface $userinterface)
    {
        $this->apiClassInterface = $apiClassInterface;
        $this->UserInterface = $userinterface;
        $this->S3SignatureDataIngestion = $S3SignatureDataIngestionInterface;
    }

    /**
     * Get user info
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo(Request $request)
    {
        $valid = $this->apiClassInterface->validateToken($request);
        $valid = 1;
        if ($valid) {
            $uuId = $request->uuid;
            $result = self::getUserData($uuId);
            $pageName = self::getPageName($uuId);
            $dataResponse = array('response' => $result, 'status' => 'Failed', 'message' => 'No data found','uuid'=>$uuId);
            if (sizeof($result) > 0) {
                $result[0]['user_dob'] = (isset($result[0]['user_dob']) && !empty($result[0]['user_dob'])) ?date("d/m/Y", strtotime($result[0]['user_dob'])) : null;
                $result[0]['spouse_dob'] = (isset($result[0]['spouse_dob']) && !empty($result[0]['spouse_dob'])) ? date("d/m/Y", strtotime($result[0]['spouse_dob'])):null;
                $result[0]['uuid'] = $uuId;
                $userId = $result[0]['id'];
                $questionnaire = $this->getUserQuestionnaireData($userId);
                $result[0]['questionnaire'] = $questionnaire;
                $result[0]['page_name'] = $pageName;
                $dataResponse = array('response' => $result, 'status' => 'Success');
            }
        } else {
            $dataResponse = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }

    /**
     * Get user data
     *
     * @param $uuId
     * @return array
     */
    public function getUserData($uuId)
    {
        $users = User::where(['user_uuid' => $uuId])
            ->leftJoin('user_banks', 'users.id', '=', 'user_banks.user_id')
            ->leftJoin('banks', 'user_banks.bank_id', '=', 'banks.id')
            ->leftJoin('signatures','users.id','signatures.user_id')
            ->leftJoin('user_spouses_details','users.id','user_spouses_details.user_id')
            ->leftJoin('user_address_details as uad','users.id','uad.user_id')
            ->select('users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.telephone',
                'users.dob as user_dob',
                'banks.id as bank_id',
                'banks.bank_code',
                'banks.bank_name',
                'signatures.s3_file_path as signature_image',
                'user_spouses_details.spouses_first_name',
                'user_spouses_details.spouses_last_name',
                'user_spouses_details.signature as spouse_sign',
                'user_spouses_details.dob as spouse_dob',
                'uad.postcode',
                'uad.address_line1',
                'uad.town',
                'uad.county',
                'uad.country',
                'uad.address_line3',
                'user_banks.is_joint',
                'user_banks.bank_sort_code as sort_code',
                'user_banks.bank_account_number as account_number'
            )->get();
        $data = [];
        if (sizeof($users->toArray()) > 0) {
            $data = $users->toArray();
        }

        if (isset($data[0]['signature_image'])){
            $signatureAwsUrl = $data[0]['signature_image'];
            $userSignature = $this->S3SignatureDataIngestion->getSignatureData($signatureAwsUrl);
            $data[0]['signature_image'] = isset($userSignature) ? $userSignature : '';
        }

        if (isset($data[0]['spouse_sign'])){
            $signatureAwsUrl = $data[0]['spouse_sign'];
            $partnerSignature = $this->S3SignatureDataIngestion->getSignatureData($signatureAwsUrl);
            $data[0]['spouse_sign'] = isset($partnerSignature) ? $partnerSignature : '';
        }

        return $data;
    }
    public function getUserFlpData($userId)
    {
        $users =  User::where('users.id',$userId)
            ->leftJoin('user_banks', 'users.id', '=', 'user_banks.user_id')
            ->leftJoin('banks', 'user_banks.bank_id', '=', 'banks.id')
            ->leftJoin('signatures','users.id','signatures.user_id')
            ->leftJoin('signature_details','users.id','signature_details.user_id')
            ->leftJoin('user_spouses_details','users.id','user_spouses_details.user_id')
            ->leftJoin('user_extra_details as uextd','users.id','uextd.user_id')
            ->select('users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.telephone',
                'users.dob as user_dob',
                'banks.id as bank_id',
                'banks.bank_code',
                'banks.bank_name',
                'signatures.s3_file_path as signature_image',
                'user_spouses_details.spouses_first_name',
                'user_spouses_details.spouses_last_name',
                'user_spouses_details.signature as spouse_sign',
                'user_spouses_details.dob as spouse_dob',
                'uextd.postcode',
                'uextd.street',
                'uextd.town',
                'uextd.county',
                'uextd.country',
                'uextd.housenumber',
                'uextd.housename',
                'uextd.address3',
                'user_banks.is_joint',
                'user_banks.bank_sort_code as sort_code',
                'user_banks.bank_account_number as account_number',
                'signature_details.previous_address_line1',
                'signature_details.previous_address_line2',
                'signature_details.previous_address_province',
                'signature_details.previous_address_city',
                'signature_details.previous_address_country',
                'signature_details.previous_postcode'
            )
            ->where('users.id',$userId)
            ->get();
        $data = [];
        if (sizeof($users->toArray()) > 0) {
            $data = $users->toArray();
        }
        if (isset($data[0]['signature_image'])){
            $signatureAwsUrl = $data[0]['signature_image'];
            $userSignature = $this->S3SignatureDataIngestion->getSignatureData($signatureAwsUrl);
            $data[0]['signature_image'] = isset($userSignature) ? $userSignature : '';
        }

        if (isset($data[0]['spouse_sign'])){
            $signatureAwsUrl = $data[0]['spouse_sign'];
            $partnerSignature = $this->S3SignatureDataIngestion->getSignatureData($signatureAwsUrl);
            $data[0]['spouse_sign'] = isset($partnerSignature) ? $partnerSignature : '';
        }

        return $data;
    }

    /**
     * Save un qualified
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnqualifiedData(Request $request)
    {
        $valid = $this->apiClassInterface->validateToken($request);
        if ($valid) {
            if (isset($request->uuid) && !empty($request->uuid)) {
                $visitorId = SplitUuid::where(['uuid' => $request->uuid])->first();
                $visitorId = isset($visitorId->visitor_id)? $visitorId->visitor_id: null;
                if ($visitorId) {
                    VisitorUnqualified::updateOrCreate(
                        ['visitor_id' => $visitorId, 'question_id' => $request->question_id, 'answer_id' => $request->answer_id],
                        ['visitor_id' => $visitorId, 'question_id' => $request->question_id, 'answer_id' => $request->answer_id],
                    );
                    $dataResponse = array('response' => 'Data  saved successfully', 'status' => 'Success');
                } else {
                    $dataResponse = array('response' => 'No visitor found', 'status' => 'Failed');
                }
            } else {
                $dataResponse = array('response' => 'UUID is missing', 'status' => 'Failed');
            }
        } else {
            $dataResponse = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }

    /**
     * Get user Questionnaire answer data
     *
     * @param $uuId
     * @return array
     */

    public function getUserQuestionnaireData($userId)
    {
        $data = UserQuestionnaireAnswers::where(['user_id' => $userId])
            ->leftJoin('questionnaires', 'user_questionnaire_answers.questionnaire_id', '=', 'questionnaires.id')
            ->leftJoin('questionnaire_options', 'user_questionnaire_answers.questionnaire_option_id', '=', 'questionnaire_options.id')
            ->whereIn('user_questionnaire_answers.questionnaire_id', [4, 5, 7, 8, 9, 10, 11, 14, 15,16,17,18])
            ->select('questionnaires.id as qId', 'questionnaires.title as question', 'questionnaire_options.value as answer', 'user_questionnaire_answers.input_answer',
                'user_questionnaire_answers.questionnaire_option_id as qoId')
            ->groupBy('user_questionnaire_answers.questionnaire_option_id')
            ->get();
        if (sizeof($data->toArray()) > 0) {
            $temp = [];
            foreach ($data as $each) {
                if ($each->qId != 10) {
                    $result [$each->qId] = ['question' => $each->question, 'answer' => $each->answer, 'input_answer' => $each->input_answer, 'answerId'=>$each->qoId];
                }
                else {
                    if($each->qoId == '24'){
                        if($each->input_answer != null){
                            $answer24 = $each->answer.' - Yes, Did the bank provided an interpreter? - '.ucfirst($each->input_answer);
                            array_push($temp, $answer24);
                        }
                        else{
                           $answer24 = $each->answer.' - Yes';
                            array_push($temp, $answer24); 
                        }
                    }
                    else if($each->qoId == '19' && $each->input_answer != null)
                    {
                        array_push($temp, $each->answer.' - '.str_replace('_', ' ', ucfirst($each->input_answer)));
                    }
                    else
                    {
                        array_push($temp, $each->answer);
                    }

                }
            }
            $questionTen = implode(', ', $temp);
            $result[10] = ['question' => '', 'answer' => $questionTen, 'input_answer' => ''];
        } else {
            $result = [];
        }
        return $result;
    }

    public function getFollowUpUserInfo(Request $request)
    {
        $valid = $this->apiClassInterface->validateToken($request);
        $valid = 1;
        if ($valid) {
            $userToken = $request->user_token;
            $userId = User::where('token',$userToken)->pluck('id')->first();
            $result = isset($userId) ? self::getUserFlpData($userId) : [];
            $dataResponse = array('response' => $result, 'status' => 'Failed', 'message' => 'No data found','userId'=>$userId);
            if (sizeof($result) > 0) {
                $result[0]['user_dob'] = (isset($result[0]['user_dob']) && !empty($result[0]['user_dob'])) ?date("d/m/Y", strtotime($result[0]['user_dob'])) : null;
                $result[0]['spouse_dob'] = (isset($result[0]['spouse_dob']) && !empty($result[0]['spouse_dob'])) ? date("d/m/Y", strtotime($result[0]['spouse_dob'])):null;
                $userId = $result[0]['id'];
                $questionnaire = $this->getUserQuestionnaireData($userId);
                $result[0]['questionnaire'] = $questionnaire;
                $dataResponse = array('response' => $result, 'status' => 'Success');
            }
        } else {
            $dataResponse = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }

    /**
     * Get page name
     *
     * @param $uuId
     * @return mixed
     */
    public function getPageName($uuId)
    {
        $result = SplitUuid::where(['uuid' => $uuId])
            ->join('visitors', 'split_uuid.visitor_id', '=', 'visitors.id')
            ->join('split_info', 'visitors.split_id', '=', 'split_info.id')
            ->first();
        return $result->split_name;
    }

    /**
     * Store unsubscribe users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeUnsubscribeUser(Request $request)
    {

        $valid = $this->apiClassInterface->validateToken($request);
        
        if ($valid) {
            $reasons = [
                'ONLY_COMPLETE_POST' => 'Please send me my claims documentation via the post to complete',
                'ONLY_COMPLETE_PHONE' => 'Please call me to complete over the phone.',
                'NOT_INTERESTED' => "Do not contact me. I'm no longer interested in proceeding with my claim."
            ];
            $users = User::where(['user_uuid' => $request->uuid])->first();

            if (isset($users->id) && !empty($users->id)) {
               
                $obj = new FollowupRepository();
                
                UnsubscribeUser::updateOrCreate(['user_id' => $users->id],
                    ['unsubscribe_email' => 1, 'unsubscribe_sms' => 1, 'reason_short' => $request->reason_short, 'reason' => $reasons[$request->reason_short]]);


                $obj->cancelScheduledSMS($users->id);
                $obj->cancelScheduledEmail($users->id);
                $dataResponse = array('response' => 'Successfully unsubscribed', 'status' => 'Success');
               
            } else {
                $dataResponse = array('response' => 'Invalid uuid', 'status' => 'Failed');
            }
        } else {
            $dataResponse = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }
    public function getFollowUpUserList()
    {
        
        $userS1 = $this->UserInterface->getFollowUpUserDetailsS1();        
        $userS2 = $this->UserInterface->getFollowUpUserDetailsS2();        
        $userS3 = $this->UserInterface->getFollowUpUserDetailsS3();        
       
       
        if($userS1['data']){
            $user = $userS1;            
            dispatch(new SendSMSJob($user));
        }
        if($userS2['data']){           
            $user = $userS2;            
            dispatch(new SendSMSJob($user));
        }
        if($userS2['data']){
            $user = $userS2;            
            dispatch(new SendSMSJob($user));
        }
    }

    public function removeUserSignature(Request $request)
    {
        $type = 'api:signature-api';
        $source = 'CRM';

        $valid = $this->apiClassInterface->validateToken($request);
        $userId = $request->user_id ?? 0;

        if ($valid) {
            if ($userId == null || $userId == 0) {
                $this->setApiStatus(
                    0,
                    $type,
                    'Failed, Field `user_id` is missing.',
                    $source
                );

                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'Field `user_id` is missing.'
                ], 400);
            }

            if ($userId != null) {
                if (!$this->isPermitted($userId, $type, $source)) {
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Sorry API execution was skipped.'
                    ]);
                }

                $userData = \DB::table('signatures')
                            ->leftJoin('users', 'users.id', '=', 'signatures.user_id')
                            ->where('user_id', $userId)
                            ->get();

                
                $userUid = $userData[0]->user_uuid ?? '';
                
                if (count($userData) == 0) {
                    $this->setApiStatus(
                        $userId,
                        $type,
                        "Failed, User doesn't exist.",
                        $source
                    );

                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'User doesn\'t exist.'
                    ]);
                }

                $signatureFilePath = $userData[0]->s3_file_path ?? '';
                if ($signatureFilePath != null) {
                    $path = explode('.amazonaws.com/', $signatureFilePath);
                    $key = $path[1] ?? null;
                    $signatureData = $this->getS3Data($key);
                    $xml = new \SimpleXMLElement($signatureData);
                    $signatureData = (string) $xml->signature;
                    $signatureDataUrl = $this->S3SignatureDataIngestion->reuploadSignatuteToS3($signatureData, $userUid, "user");
                    
                    #SIGNATURE HISTORY
                    \DB::table('signature_histories')->insert([
                        'user_id' => $userId,
                        's3_file_path' => $signatureDataUrl,
                        'source' => $source
                    ]);

                    #DELETE SIGNATURE
                    \DB::table('signatures')->where([
                        'user_id' => $userId,
                    ])->update(
                        [
                            'signature_image' => null, 
                            's3_file_path' => null
                    ]);
                    
                    #MARKED AS INCOMPLETE
                    \DB::table('user_extra_details')->where([
                        'user_id' => $userId,
                    ])->update(['complete_status' => 0]);

                    $this->setApiStatus(
                        $userId,
                        $type,
                        $signatureDataUrl,
                        $source
                    );

                    return response()->json([
                        'status' => 'Success',
                        'msg' => 'Signature removed.'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'user_id field cannot to be empty.'
                ]);
            }
        } else {
            $this->setApiStatus(
                $userId,
                $type,
                'Failed, Authentication Failed.',
                $source
            );

            return response()->json([
                'status' => 'Failed',
                'msg' => 'Authentication Failed.'
            ]);
        }
    }

    public function setApiStatus($userId, $type, $content, $source)
    {
        \DB::table('crm_api_status')->insert([
            'user_id' => $userId,
            'content' => $content,
            'source' => $source,
            'type' => $type
        ]);
    }

    public function testCake(Request $userId) {

        $userId = $request->user_id ?? 0;

        dispatch(new PostLeadsToCake($userId, 'TEST', 0));
        echo "Done";
    }

    public function generatePdf($userId)
    {
        //echo "Welcome!".$userId;
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
        dispatch(new GeneratePdf($userId));
        if(!empty($userId)){
            dispatch(new PostLeadsToCake($userId, $recordStatus, $milestone_status));
        }
    }

    public function getS3Data($key)
    {
        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');
        $key = str_ireplace($s3BucketName . '/', '', $key);

        $s3 = new Aws\S3\S3Client([
            'region' => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key' => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);

        $result = $s3->getObject([
            'Bucket' => $s3BucketName,
            'Key' => $key,
            'ACL' => 'public-read'
        ]);

        $arrResult = (array)$result;
        foreach ($arrResult as $key => $arrValue) {
            return $arrValue['Body']->__toString();
        }

        return null;
    }

    public function pdfRemovalApi(Request $request) 
    {
        $type = 'api:pdfremoval-api';
        $source = 'CRM';

        $valid = $this->apiClassInterface->validateToken($request);
        $userId = $request->user_id ?? 0;

        if ($valid) {
            if ($userId == null || $userId == 0) {
                $this->setApiStatus(
                    0,
                    $type,
                    'Failed, Field `user_id` is missing.',
                    $source
                );

                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'Field `user_id` is missing.'
                ], 400);
            }

            if ($userId != null) {

                if (!$this->isPermitted($userId, $type, $source)) {
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Sorry API execution was skipped.'
                    ]);
                }
                $userData = \DB::table('users')
                ->where('id', $userId)
                ->get();
                if(count($userData)==0){
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Sorry .The user is not exist!'
                    ]); 
                }


                $buyerResponseData = LeadDoc::updateOrCreate(
                    [   'user_id' => $userId],
                    [   "bank_loa_pdf_files" => null,
                        "questionnaire_pdf_files"=>null,
                        "witness_statement_pdf"=>null,
                        "statement_of_truth_pdf"=>null,
                    ]
                );

                $buyerResponseData = LeadDocBase::updateOrCreate(
                    [   'user_id' => $userId],
                    [   "bank_loa_pdf_files_base" => null,
                        "questionnaire_pdf_files_base"=>null,
                        "witness_statement_pdf_base"=>null,
                        "statement_of_truth_pdf_base"=>null,
                    ]
                );

                $this->setApiStatus(
                    $userId,
                    $type,
                    'PDF files has been removed successfully.',
                    $source
                );

                return response()->json([
                    'status' => 'Success',
                    'msg' => 'PDF files has been removed successfully.'
                ]);

            } else {
                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'user_id field cannot to be empty.'
                ]);
            }
        } else {
            $this->setApiStatus(
                $userId,
                $type,
                'Failed, Authentication Failed.',
                $source
            );

            return response()->json([
                'status' => 'Failed',
                'msg' => 'Authentication Failed.'
            ]);
        }
    }

    public function isPermitted($userId, $type, $source) 
    {
        $enviornment = env('APP_ENV');

        if (strtolower($enviornment) === 'pre') {
            $userInfo = \DB::table('users')->where('id', $userId)->first();

            if (!$userInfo) {
                return false;
            }

            if ($userInfo->record_status != 'TEST') {
                $this->setApiStatus(
                    $userId,
                    $type,
                    'Failed, API Execution was skipped.',
                    $source
                );
                return false;
            }
        }

        return true;
    }

    public function pdfRegenerateApi(Request $request){
    //public function pdfRegenerateApi(){
        $type = 'api:pdfregeneration-api';
        $source = 'CRM';
        $userRepository = new UserRepository();
        if(!isset($bankdata)){
            $bankdata = [];
        }
        $valid = $this->apiClassInterface->validateToken($request);
        
        $userId = $request->user_id ?? 0;
        $pdfExist= LeadDoc::where(['user_id'=>$userId])
        ->whereNotNull('bank_loa_pdf_files')
        ->first();
        if($valid){

            if ($userId == null || $userId == 0) {
                $this->setApiStatus(
                    0,
                    $type,
                    'Failed, Field `user_id` is missing.',
                    $source
                );

                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'Failed `user_id` is missing.'
                ], 400);
            }
            if(isset($pdfExist) && !empty($pdfExist)){
                $this->setApiStatus(
                    0,
                    $type,
                    'PDF already exist, please remove them first',
                    $source
                );

                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'PDF already exist, please remove them first'
                ], 400);

            }
            if ($userId != null) {
                if (!$this->isPermitted($userId, $type, $source)) {
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Sorry API execution was skipped.'
                    ]);
                }
                $userData = \DB::table('users')
                ->where('id', $userId)
                ->get();
                if(count($userData)==0){
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Sorry .The user is not exist!'
                    ]); 
                }
                $userBanks = $userRepository->getUserDetailsFromUserId($userId);
                dispatch(new RegenerateLoaPDFApi($request)); 
                dispatch(new RegeneratePdf($request));
                
                $this->setApiStatus(
                    $userId,
                    $type,
                    'PDF files has been generated successfully.',
                    $source
                );

                return response()->json([
                    'status' => 'Success',
                    'msg' => 'PDF files has been generated successfully.'
                ]);
            }

        }else {
            $this->setApiStatus(
                $userId,
                $type,
                'Failed, Authentication Failed.',
                $source
            );

            return response()->json([
                'status' => 'Failed',
                'msg' => 'Authentication Failed.'
            ]);
       }
        
        //$userId = 10;

      // dispatch(new RegeneratePdf($request));
        
    }
    public function leadSubmissionApi(Request $request)
    {
        $type = 'CRM';
        $source = 'LIVE';
        $requiredFields = ['first_name', 'last_name', 'address_id', 'post_code', 'email'];
        $errorMsg = "";
        $errorReqFields = [];
        
        foreach($requiredFields as $requiredField) {
            if(!isset($request->$requiredField) || empty($request->$requiredField)) {
                $errorReqFields[] = $requiredField;
            }
        }
        
        if (count($errorReqFields) > 0 ) {
            $errorfieldNameStr = implode(', ', $errorReqFields);
            $errorMsg .= 'Missing fields/values -' . $errorfieldNameStr;
        }
            if(count($request->banks)<1)
            {
                $errorMsg .= 'Select atleast one bank';
            }
            if(sizeof($request->question_answers)<12)
            {
                $errorMsg .= 'All question must be answered';
            }
            foreach($request->question_answers as $key =>$answer)
            {
                if(empty($answer)|| $answer=="")
                {
                    $errorMsg .= 'All question must be answered';
                    break;
                }
            }
           /* if(!empty($errorMsg))
            {
                   /* $this->setApiRequest(
                        0,
                        $type,
                        'Error: ' . $errorMsg,
                        $source,
                        $request
                    );
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Error: ' . $errorMsg,
                    ], 400);
            }*/
            $uuid = $this->UserInterface->GenerateUuid();
            dispatch(new UserInsertion($request->all(), $uuid));
            dispatch(new SaveSignature($request->all(), $uuid));
            dispatch(new SaveQuestion($request->all(), $uuid));

            

    }
    public function generatePdfApi(Request $request)
    {
        dispatch(new GenerateLOApdfApi($request->all()));
        dispatch(new GeneratePdfApi($request->all()));
    }
    public function deleteSignature(Request $request)
    {
        $type = 'api:signature-api';
        $source = 'CRM';

        $valid =$this->apiClassInterface->validateToken($request);
        $userId = $request->user_id ?? 0;

        if ($valid) {
            if ($userId == null || $userId == 0) {
                $this->setApiStatus(
                    0,
                    $type,
                    'Failed, Field `user_id` is missing.',
                    $source
                );

                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'Field `user_id` is missing.'
                ], 400);
            }

            if ($userId != null) {
                if (!$this->isPermitted($userId, $type, $source)) {
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Sorry API execution was skipped.'
                    ]);
                }

                $userData = \DB::table('signatures')
                            ->leftJoin('users', 'users.id', '=', 'signatures.user_id')
                            ->where('user_id', $userId)
                            ->get();
               
                
                $userUid = $userData[0]->user_uuid ?? '';
                
                if (count($userData) == 0) {
                    $this->setApiStatus(
                        $userId,
                        $type,
                        "Failed, User doesn't exist.",
                        $source
                    );

                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'User doesn\'t exist.'
                    ]);
                }

                $signatureFilePath = $userData[0]->s3_file_path ?? '';
                if ($signatureFilePath != null) {
                    $path = explode('.amazonaws.com/', $signatureFilePath);
                    $key = $path[1] ?? null;
                    $signatureData = $this->getS3Data($key);
                    $xml = new \SimpleXMLElement($signatureData);
                    $signatureData = (string) $xml->signature;
                    $signatureDataUrl = $this->S3SignatureDataIngestion->reuploadSignatuteToS3($signatureData, $userUid, "user");
                    var_dump($signatureDataUrl);
                    #SIGNATURE HISTORY
                    \DB::table('signature_histories')->insert([
                        'user_id' => $userId,
                        's3_file_path' => $signatureDataUrl,
                        'source' => $source
                    ]);

                    #DELETE SIGNATURE
                    \DB::table('signatures')->where([
                        'user_id' => $userId,
                    ])->update(
                        [
                            'signature_image' => null, 
                            's3_file_path' => null
                    ]);
                    
                    #MARKED AS INCOMPLETE
                    \DB::table('user_extra_details')->where([
                        'user_id' => $userId,
                    ])->update(['complete_status' => 0]);

                    $this->setApiStatus(
                        $userId,
                        $type,
                        $signatureDataUrl,
                        $source
                    );

                    return response()->json([
                        'status' => 'Success',
                        'msg' => 'Signature removed.'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'user_id field cannot to be empty.'
                ]);
            }
        } else {
            $this->setApiStatus(
                $userId,
                $type,
                'Failed, Authentication Failed.',
                $source
            );

            return response()->json([
                'status' => 'Failed',
                'msg' => 'Authentication Failed.'
            ]);
        }
    }
    public function restoreSignature(Request $request)
    {
        $type = 'api:signature-api';
        $source = 'CRM';

        $valid =1;//$this->apiClassInterface->validateToken($request);
        $userId = $request->user_id ?? 0;

        if ($valid) {
            if ($userId == null || $userId == 0) {
                $this->setApiStatus(
                    0,
                    $type,
                    'Failed, Field `user_id` is missing.',
                    $source
                );

                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'Field `user_id` is missing.'
                ], 400);
            }
            dispatch(new RestoreSignature($request->all()));

        }
        else {
            $this->setApiStatus(
                $userId,
                $type,
                'Failed, Authentication Failed.',
                $source
            );

            return response()->json([
                'status' => 'Failed',
                'msg' => 'Authentication Failed.'
            ]);
        }

    }
    public function pdfRegenerationApi(Request $request){
        //public function pdfRegenerateApi(){
            $type = 'api:pdfregeneration-api';
            $source = 'CRM';
            $userRepository = new UserRepository();
            if(!isset($bankdata)){
                $bankdata = [];
            }
            $valid = $this->apiClassInterface->validateToken($request);
            
            $userId = $request->user_id ?? 0;
            $pdfExist= LeadDoc::where(['user_id'=>$userId])
            ->whereNotNull('bank_loa_pdf_files')
            ->first();
            if($valid){
    
                if ($userId == null || $userId == 0) {
                    $this->setApiStatus(
                        0,
                        $type,
                        'Failed, Field `user_id` is missing.',
                        $source
                    );
    
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'Failed `user_id` is missing.'
                    ], 400);
                }
                if(isset($pdfExist) && !empty($pdfExist)){
                    $this->setApiStatus(
                        0,
                        $type,
                        'PDF already exist, please remove them first',
                        $source
                    );
    
                    return response()->json([
                        'status' => 'Failed',
                        'msg' => 'PDF already exist, please remove them first'
                    ], 400);
    
                }
                if ($userId != null) {
                    if (!$this->isPermitted($userId, $type, $source)) {
                        return response()->json([
                            'status' => 'Failed',
                            'msg' => 'Sorry API execution was skipped.'
                        ]);
                    }
                    $userData = \DB::table('users')
                    ->where('id', $userId)
                    ->get();
                    if(count($userData)==0){
                        return response()->json([
                            'status' => 'Failed',
                            'msg' => 'Sorry .The user is not exist!'
                        ]); 
                    }
                    $userBanks = $userRepository->getUserDetailsFromUserId($userId);
                    dispatch(new PDFRegenerationApi($request)); 
                    
                    $this->setApiStatus(
                        $userId,
                        $type,
                        'PDF files has been generated successfully.',
                        $source
                    );
    
                    return response()->json([
                        'status' => 'Success',
                        'msg' => 'PDF files has been generated successfully.'
                    ]);
                }
    
            }else {
                $this->setApiStatus(
                    $userId,
                    $type,
                    'Failed, Authentication Failed.',
                    $source
                );
    
                return response()->json([
                    'status' => 'Failed',
                    'msg' => 'Authentication Failed.'
                ]);
           }
            
         
            
        }
        public function getUserAddress(Request $request)
        {
            $userExtra = UserExtraDetail::select('user_id')->get()->toArray();
            $extra_details = array_unique(array_column($userExtra,'user_id'));
            $addressTypes = SignatureDetails::select('signature_id')->get()->toArray();
            $addressType = array_unique(array_column($addressTypes,'signature_id'));
            $userSig = SignatureDetails::select('user_id')->whereIn('signature_id',$addressType)->get()->toArray();
            $sig_details =array_unique(array_column($userSig,'user_id'));
            $extra_details = array_diff($extra_details, $sig_details);
            $sig_details = array_diff($sig_details, $extra_details);
            dd($sig_details);

        }


}
