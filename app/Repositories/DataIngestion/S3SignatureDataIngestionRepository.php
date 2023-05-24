<?php


namespace App\Repositories\DataIngestion;


use App\Repositories\Interfaces\S3SignatureDataIngestionInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Aws;

/**
 * Class S3SignatureDataIngestionRepository
 *
 * @package App\Repositories\DataIngestion
 */
class S3SignatureDataIngestionRepository implements S3SignatureDataIngestionInterface
{

    /**
     * Store
     *
     * @param $data
     * @param $visitorParameters
     */
    public function userS3SignatureStore($signatureData, $user_id, $sign_holder = 'user')
    {
      $signHolder  = ($sign_holder == 'user') ? 'user_' : 'partner_';
      if (env('APP_ENV') == 'local') 
      {
          $sign_storage_path = storage_path() . '/app/uploads/xml/signature/';
      } else 
      {
          $sign_storage_path = storage_path().'/';
      }
      $signFileName         = $sign_storage_path.$signHolder.$user_id.'.xml';
      $sign_path = storage_path() . '/app/uploads/xml/signature/';
      $xmlFileData    = $this->covertSignToXml($signatureData);
      $xmlFile = explode("/", $signFileName);
        if(isset($xmlFile)){
            $xmlFileName = $xmlFile[sizeof($xmlFile)-1];
        }
      $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'local' || $APP_ENV == 'dev') {
            $s3_basic_path = "pba/signature/dev";
        } elseif($APP_ENV == 'live' || $APP_ENV == 'prod'){
            $s3_basic_path = "pba/signature/live";
        }
        elseif($APP_ENV == 'pre'){
            $s3_basic_path = "pba/signature/pre";
        }
        
        $s3_path = $this->saveFileDirectIntoS3($xmlFileName, $xmlFileData, $s3_basic_path);

        return $s3_path;
    }
    
    public function saveSignatuteToS3($signatureData, $user_uuid, $sign_holder)
    {
        $signHolder   = ($sign_holder == 'user') ? 'user_' : 'partner_';
        $signFileName = $signHolder.$user_uuid.'.xml';
        $sign_path    = storage_path() . '/app/uploads/xml/signature/';
        $xmlFileData  = $this->covertSignToXml($signatureData);
        $xmlFileName  = $signFileName;
        $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'local' || $APP_ENV == 'dev') {
            $s3_basic_path = "pba/signature/dev";
        } elseif($APP_ENV == 'live' || $APP_ENV == 'prod'){
            $s3_basic_path = "pba/signature/live";
        }
        elseif($APP_ENV == 'pre'){
            $s3_basic_path = "pba/signature/pre";
        }

        $s3_path = $this->saveFileDirectIntoS3($xmlFileName, $xmlFileData, $s3_basic_path);
        return $s3_path;
    }

    public function reuploadSignatuteToS3($signatureData, $user_uuid, $sign_holder)
    {
        $signHolder   = ($sign_holder == 'user') ? 'user_' : 'partner_';
        $signFileName = $signHolder. '' . time() . '_' . $user_uuid.'.xml';
        $sign_path    = storage_path() . '/app/uploads/xml/signature/';

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ClientDetails></ClientDetails>');
        $covertedFile = base64_encode($signatureData);
        $xml->addAttribute('version', '1.0');
        $xml->addChild('signature', $covertedFile);
        $xmlFileData = $xml->asXML();

        $xmlFileName  = $signFileName;
        $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'local' || $APP_ENV == 'dev') {
            $s3_basic_path = "pba/signature/dev";
        } elseif($APP_ENV == 'live' || $APP_ENV == 'prod' ){
            $s3_basic_path = "pba/signature/live";
        }
        elseif($APP_ENV == 'pre'){
            $s3_basic_path = "pba/signature/pre";
        }

        $s3_path = $this->saveFileDirectIntoS3($xmlFileName, $xmlFileData, $s3_basic_path);
        return $s3_path;
    }
    /**
     * Convert sign to xml
     *
     * @param $signatureData
     * @param $filename
     * @return array
     */
    public function covertSignToXml($signatureData)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ClientDetails></ClientDetails>');
        $covertedFile = base64_encode(file_get_contents($signatureData));

        $xml->addAttribute('version', '1.0');
        $xml->addChild('signature', $covertedFile);
        
        // $xmlFile = $filename;
        // $xml->saveXML($xmlFile);
        $xml_content = $xml->asXML();

        return $xml_content;
    }

    /**
     * Save file into s3
     *
     * @param $fileName
     * @param $filePath
     * @param $folderPath
     * @return mixed
     */
    public function saveFileIntoS3($fileName, $filePath, $folderPath)
    {
        ## S3 BUCKET #############################
        Log::info("fileName..." . $fileName . "filrpath....." . $filePath . "folderpath..." . $folderPath);

        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');

        $s3 = new Aws\S3\S3Client([
            'region' => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key' => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);

        $result = $s3->putObject([
            'Bucket' => env('AWS_BUCKET'),
            //'Key'           => $this->s3BucketFolderName . "/" . $fileName,
            'Key' => $folderPath . '' . $fileName,
            'SourceFile' => $filePath,
            'ACL' => 'private'
        ]);

        $arrResult = (array)$result;

        foreach ($arrResult as $key => $arrValue) {
            if (preg_replace('/[^\da-z]/i', "", $key) == "AwsResultdata" && isset($arrValue["@metadata"]["statusCode"]) && $arrValue["@metadata"]["statusCode"] == "200") {
                return $arrValue["@metadata"]["effectiveUri"];
            }
        }

    }

    /**
     * Get signature data
     *
     * @param $awsUrl
     * @return string
     */
    public function getSignatureData($awsUrl)
    {
        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');

        $url_split = parse_url($awsUrl);
        $url_key  = explode('/',$url_split['path'],3);

        $s3 = new Aws\S3\S3Client([
            'region'  => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key'    => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);
        $resultData = $s3->getObject(array(
            'Bucket' => $s3BucketName,
            'Key'    => $url_key[2],
        ));

        $bodyData = $resultData->get('Body');
        $bodyData->rewind();
        $contentData = $bodyData->read($resultData['ContentLength']);

        $deXml      = simplexml_load_string($contentData);
        $deJson     = json_encode($deXml);
        $xml_array  = json_decode($deJson, TRUE);
        $xml_signature = 'data:image/png;base64,'.$xml_array['signature'];
        return $xml_signature;
    }

    public function saveFileDirectIntoS3($fileName, $content, $folderPath)
    {
        ## S3 BUCKET #############################
        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');
        ## S3 BUCKET #############################

        $s3 = new Aws\S3\S3Client([
            'region'  => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key'    => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);

        $folderPath = trim($folderPath, '/');
        $result = $s3->putObject([
            'Bucket'        => env('AWS_BUCKET'),
            'Key'           => $folderPath.'/'.$fileName,
            'Body' => $content,
            'ContentDisposition'=>"inline",
            'ContentType'=>"application/vnd.adobe.pdfxml",
            //'ACL'           => 'public-read'
        ]);

        $arrResult = (array)$result;

        foreach ($arrResult as $key => $arrValue) {
            if(preg_replace('/[^\da-z]/i' , "" , $key) == "AwsResultdata" && isset($arrValue["@metadata"]["statusCode"]) && $arrValue["@metadata"]["statusCode"] == "200"){
                return $arrValue["@metadata"]["effectiveUri"];
            }
        }
    }
}

