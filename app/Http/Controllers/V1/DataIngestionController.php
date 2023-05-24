<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Repositories\Interfaces\ApiClassInterface;
use App\Repositories\Interfaces\HistoryInterface;
use App\Jobs\DataIngestionPipeline;
use App\Models\User;
use App\Repositories\Interfaces\LPDataIngestionInterface;
use App\Repositories\Interfaces\QuestionnairesInterface;
use App\Repositories\Interfaces\S3SignatureDataIngestionInterface;
use App\Repositories\UserDocumentsRepository;
use App\Repositories\DataIngestion\FollowupDataIngestionRepository;

/**
 * Class DataIngestionController
 *
 * @package App\Http\Controllers\V1
 */
class DataIngestionController extends Controller
{

    /**
     * DataIngestionController constructor.
     *
     *
     * @param ApiClassInterface $apiClassInterface
     * @param HistoryInterface $historyInterface
     * @param LPDataIngestionInterface $lPDataIngestionInterface
     * @param QuestionnairesInterface $questionnairesInterface
     */
    public function __construct(ApiClassInterface $apiClassInterface, HistoryInterface $historyInterface,
                                LPDataIngestionInterface $lPDataIngestionInterface, QuestionnairesInterface $questionnairesInterface,S3SignatureDataIngestionInterface $S3SignatureDataIngestionInterface)
    {
        $this->apiClassInterface = $apiClassInterface;
        $this->historyInterface = $historyInterface;
        $this->lPDataIngestionInterface = $lPDataIngestionInterface;
        $this->questionnairesInterface = $questionnairesInterface;
        $this->S3SignatureDataIngestion  = $S3SignatureDataIngestionInterface;
    }

    /**
     * Piple line
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function pipeline(Request $request)
    {
      
        $data = $request->all();
        $user_uuid = (isset($data['visitor_parameters']) && isset($data['visitor_parameters']['uuid'])) ? $data['visitor_parameters']['uuid'] : '';
       
        $valid = $this->apiClassInterface->validateToken($request);
        if ($valid == 1) {
           
            $user_id = null;
            $currentTime = Carbon::now()->format('Y-m-d H:i:s');
            $data['currentTime'] = $currentTime;
            if (isset($data['upload_data']) && $data['message_type'] == 'user_docs_store') {
                $response = self::jsonPipeline($data);
                $data['user_document_data']['captureType'] = $response['captureType'];
                $data['user_document_data']['user_identification_type'] = $response['user_identification_type'];
                $data['user_document_data']['
                '] = $response['user_identification_s3'];
            }
            //store user and spouse signature to S3
            if(@$data['message_type'] == 'signature_store'){
                $previousData = [];
                if (isset($data['form_data']['previousName']) && $data['form_data']['previousName'] != '') {
                    $previousData['previous_name'] = $data['form_data']['previousName'];
                }

                if (isset($data['form_data']['previousAddress']) && $data['form_data']['previousAddress'] != '') {
                    $previousData['previous_address'] = $data['form_data']['previousAddress'];
                }

                if (isset($data['form_data']['previousAddressData']) && $data['form_data']['previousAddressData'] != '') {
                    $previousData['previous_address_data'] = $data['form_data']['previousAddressData'];
                }

                if (isset($data['form_data']['peviousPostCode']) && $data['form_data']['peviousPostCode'] != '') {
                    $previousData['previous_post_code'] = $data['form_data']['peviousPostCode'];
                }

                $data['previous_data'] = $previousData;


                $signatureData = isset($data['signature_data'])? $data['signature_data'] : '';
                $s3_signatureData = $this->S3SignatureDataIngestion->saveSignatuteToS3($signatureData, $user_uuid,"user");
            }
            $data['s3_signatureData'] = isset($s3_signatureData)?$s3_signatureData:'';
            if (array_key_exists("visitor_parameters", $data) ) {
               
                $result = dispatch(new DataIngestionPipeline($data));
            }
            if (array_key_exists("followup_data", $data)) {
                if (isset($data['followup_data']['atp_sub2'])) {
                    $user = User::where('token', $data['followup_data']['atp_sub2'])->first();
                    if (isset($user)) {
                        $user_id = $user->id;
                        $user_uuid = $user->user_uuid;
                        $token = $user->token;
                    }
                }
            }
            //Followupdata - Save signature to s3 and push to queue
           if(isset($data['followup_data'])){
                if(@$data['message_type'] == 'followup_user_signature'){
                    $signatureData = isset($data['signature_data'])? $data['signature_data'] : '';
                   
                    //$s3_signatureData = $this->S3SignatureDataIngestion->saveSignatuteToS3($signatureData, $user_uuid,"user");
                    $s3_signatureData = $signatureData;
                }
                $data['s3_signatureData'] = isset($s3_signatureData)?$s3_signatureData:'';
                $data['token'] = isset($token)?$token:'';
                $result = dispatch(new DataIngestionPipeline($data));
            }
            
            $dataResponse = ['status' => 'Success'];
            //return['data'=>$data['message_type'],'status'=>'show message type'];
            $this->historyInterface->createApiHistory(array(
                'user_id' => $user_id,
                'user_uuid' => $user_uuid,
                'url' => 'v1\Api\DataIngestion\data-ingestion-pipeline',
                'request' => json_encode($request->all()),
                'response' => json_encode($dataResponse)
            ));
        } else {
            $dataResponse = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }
    public function jsonPipeline($data){
        $imgFile = $data['upload_data']['imageFile'];
        $captureType = @$data['upload_data']['captureType'];
        $identificationType = @$data['upload_data']['documentType'];
        $extension = explode('/', explode(':', substr($imgFile, 0, strpos($imgFile, ';')))[1])[1];   // .jpg .png     
        //return response()->json($data);
        if (in_array($extension, ['jpg', 'png', 'jpeg'])) {
            $APP_ENV =  env('APP_ENV');
            if ($APP_ENV == 'pre' || $APP_ENV == 'live') {
                    $s3_basic_path = "ID/live/";
                } else {
                    $s3_basic_path = "ID/dev/";
            }  
            $user = User::where(['user_uuid' => $data['visitor_parameters']['uuid']])->first();
            if (isset($user)) {
                $userId = $user->id;
            } else {
                $userId = 0;
            }
       
            $userDocRepo    = new UserDocumentsRepository;
                    
            $s3_retrieve_path = $userDocRepo->docUpload($userId,$extension, $imgFile, $s3_basic_path);
            $strFileContent = '\n----------\n Date: ' . date('Y-m-d H:i:s') . "\n User_id: " . $userId . "\n Extension: " . $extension . "\n s3_retrieve_path: " .  $s3_retrieve_path . '  \n';
            //$logWrite   = $logRepo->writeLog('-imagefileDirect', $strFileContent); 
            $s3_result = array(
                                'captureType' => $captureType,
                                'user_identification_type' => $identificationType,
                                'user_identification_s3'   => $s3_retrieve_path
                            );
            unset($data['user_document_data']['imageFile']);
            return $s3_result;
        }
    } 
    
}
