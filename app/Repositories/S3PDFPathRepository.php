<?php
namespace App\Repositories;

use App\Repositories\Interfaces\S3PDFPathInterface;
use Illuminate\Support\Facades\Log;
use Aws;

class S3PDFPathRepository implements S3PDFPathInterface{

    protected $s3BucketName = "pdf.bankrefundsnow.co.uk";
    protected $s3BucketRegion = "eu-west-2";
    protected $s3BucketAccessKeyId = "AKIAR7CDOEEIYGI7HKH3";
    protected $s3BucketAccessSecret = "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";

    public function storePDFPath($pdfPath,$userId)
    {
        if (env('APP_ENV') == 'local') 
      {
          $sign_storage_path = storage_path() . '/app/uploads/pdf/pdf';
      } else 
      {
          $sign_storage_path = storage_path().'/';
      }

      $pdfFileName         = $pdfPath.$userId.'.xml';
      $sign_path = storage_path() . '/app/uploads/xml/signature/';
      $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'local' || $APP_ENV == 'dev') {
            $s3_basic_path = "pba/signature/dev";
        } elseif($APP_ENV == 'live' || $APP_ENV == 'prod'){
            $s3_basic_path = "pba/signature/live";
        }elseif($APP_ENV == 'pre'){
            $s3_basic_path = "pba/signature/pre";
        }
        
        $s3_path = $this->saveFileDirectIntoS3( $pdfFileName , $s3_basic_path);

        return $s3_path;
    }
    public function saveFileDirectIntoS3($pdfFileName , $s3_basic_path)
    {
        ## S3 BUCKET #############################
        //$s3BucketName = env('AWS_BUCKET');
        //$s3BucketRegion = env('AWS_DEFAULT_REGION');
        //$s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        //$s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');
        ## S3 BUCKET #############################

        $s3 = new Aws\S3\S3Client([
            'region'  => $this->s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key'    => $this->s3BucketAccessKeyId,
                'secret' => $this->s3BucketAccessSecret,
            ]
        ]);

        $result = $s3->putObject([
            'Bucket'        => $this->s3BucketName,
            'Key'           => $folderPath.''.$fileName,
            'Body' => $content,
            'ContentDisposition'=>"inline",
            'ContentType'=>"application/vnd.adobe.pdfxml",
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