<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UserDocumentsInterface;
use Carbon\Carbon;
use App\Repositories\LogRepository;
use Aws;

use App\Models\LeadDoc;
use App\Repositories\LiveSessionRepository;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class UserDocumentsRepository implements UserDocumentsInterface
{
    public function sendUserDocuments($dataArray)
    {
        $intDocId = null;
        $user_id                    = isset($dataArray['user_id']) ? $dataArray['user_id'] : '';
        $userdocument_data             = isset($dataArray['userdocument_data'])?$dataArray['userdocument_data']: "";
        $strFileContent = '\n----------\n Date: ' . date( 'Y-m-d H:i:s' ) . "\n User Documents Page - captureType: " . $userdocument_data['captureType'] . "\n User Documents: " . $userdocument_data['user_identification_s3'] ." \n UID: " .$user_id. '  \n';
        $logRepo    = new LogRepository;
        $logWrite   = $logRepo->writeLog('-getuserDocuFilesNewOld', $strFileContent);
        //$image_64 = $data['photo']; //your base64 encoded data
        //$image_64 = $userdocument_data['imageFile'];
        $captureType        = $userdocument_data['captureType'];
        $identificationType = $userdocument_data['user_identification_type'];
        $s3_image_path      = $userdocument_data['user_identification_s3'];
        $follow_up_source   = 'live';

        
        if ($s3_image_path) {
            
            $strFileContent = '\n----------\n Date: ' . date( 'Y-m-d H:i:s' ) . "\n User_id: " . $user_id . "\n s3_image_path: " . $s3_image_path .'  \n';
            $logWrite   = $logRepo->writeLog('-docs_image_path', $strFileContent);
            $pdfdata    = array("user_identification_image_s3" => $s3_image_path);
            $leadDocData = LeadDoc::updateOrCreate(
                            [
                                'user_id' => $user_id,
                            ],
                            $pdfdata
                            );
        }

        return $intDocId;
    }
    public function docUpload($userId,$extension, $imagfile, $s3_basic_path)
    {
        try {
            $s3_enc_file_name = substr(md5('bmc_doc_' . $userId), 0, 16).".".$extension;
            $user_data = User::where('id', '=', $userId)->select('first_name','last_name','token')->first();
            $surname = (isset($user_data['last_name']) ? $user_data['last_name'] : $user_data['first_name']);
            $s3_doc_path   = $this->saveFileIntoS3($s3_enc_file_name, $imagfile, $s3_basic_path);
            $s3_retrieve_path = env('DOC_URL', 'https://doc.boilerplate/') . 'user_docs/' . $surname . '_DOC_' . $s3_enc_file_name . '?token=' . $user_data->token;
            $s3_retrieve_path = ($s3_doc_path != false) ? $s3_retrieve_path : false;
 
            $file_name = 'user_doc_data';
            $strLogContent = "**************** Date: " . date('Y-m-d H:i:s') . "USER ID: " . $userId . "**********\r\n";
            $userDetails = [
                'userid' => $userId,
                's3_path' => $s3_doc_path,
                's3_retrieve_path' => $s3_retrieve_path
            ];
            $log_data = $strLogContent . "user details:   " . json_encode(@$userDetails) . "***************\r\n";
            $logRepo    = new LogRepository;
            $logWrite   = $logRepo->writeLog($file_name, $log_data);
            return $s3_doc_path;
        } catch (\Exception $exception) {
            $msg = "User doc upload failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
            Log::warning($msg);
        }
    }
  
    public function saveFileIntoS3( $fileName, $filePath, $folderPath )
    {

        #### S3 BUCKET #############################
        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');

        ## S3 BUCKET #############################

        $s3 = new Aws\S3\S3Client([
                                        'region'        => $s3BucketRegion,
                                        'version'       => 'latest',
                                        'credentials'   => [
                                                                'key'    => $s3BucketAccessKeyId,
                                                                'secret' => $s3BucketAccessSecret,
                                                            ]
                                    ]);

        $result = $s3->putObject([
                                        'Bucket'        => $s3BucketName,
                                        'Key'           => $folderPath.''.$fileName,
                                        'SourceFile'    => $filePath,
                                        'ACL'           => 'public-read'
                                    ]);
        $arrResult = (array)$result;

        foreach ($arrResult as $key => $arrValue) {
            if(preg_replace('/[^\da-z]/i' , "" , $key) == "AwsResultdata" && isset($arrValue["@metadata"]["statusCode"]) && $arrValue["@metadata"]["statusCode"] == "200"){
                return $arrValue["@metadata"]["effectiveUri"];
            }
        }
    }
}
