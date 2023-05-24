<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws;
/**
 * Class PDFDownloadController
 *
 * @package App\Http\Controllers
 */
class PDFDownloadController extends Controller
{
    /**
     * PDFDownloadController constructor.
     *
     */
    public function __construct()
    {
        $this->middleware('PDFDownloadAuth');
    }

    /**
     * Index
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|string
     */
    public function index(Request $request)
    {
        $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'live' || $APP_ENV == 'prod') {
            $s3_basic_path = "live/";
        } elseif ($APP_ENV == 'pre') {
            $s3_basic_path = "pre/";
        } else {
            $s3_basic_path = "dev/";
        }
        
        $s3BucketName = env('AWS_BUCKET');
        $s3BucketRegion = env('AWS_DEFAULT_REGION');
        $s3BucketAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $s3BucketAccessSecret = env('AWS_SECRET_ACCESS_KEY');
        ## S3 BUCKET #############################
        // $s3BucketName = "hlcc024-pdfs";
        // $s3BucketRegion = "eu-west-2";
        // $s3BucketAccessKeyId = "AKIAR7CDOEEISI6W6TN5";
        // $s3BucketAccessSecret = "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";
        ## S3 BUCKET #############################
        $s3 = new Aws\S3\S3Client([
            'region' => $s3BucketRegion,
            'version' => 'latest',
            'credentials' => [
                'key' => $s3BucketAccessKeyId,
                'secret' => $s3BucketAccessSecret,
            ]
        ]);
dd($s3);
        $path = $request->filename;
        $s3_pdf_file = $s3->doesObjectExist($s3BucketName, $s3_basic_path . '' . $path);
        
        if ($s3_pdf_file == false) {
            $path = $request->path();
            $path = explode('/',$path);
            $path = end($path);
            $s3_pdf_file = $s3->doesObjectExist($s3BucketName, $s3_basic_path . '' . $path);
        }

        if ($s3_pdf_file == true) {
            //if (isset($s3_pdf_file)) {
            $result = $s3->getObject([
                'Bucket' => $s3BucketName,
                'Key' => ''.$s3_basic_path . '' .  $path,
                'ACL' => 'public-read'
            ]);

            $arrResult = (array)$result;
            foreach ($arrResult as $key => $arrValue) {
                if (preg_replace('/[^\da-z]/i', "", $key) == "AwsResultdata" && isset($arrValue["@metadata"]["statusCode"]) && $arrValue["@metadata"]["statusCode"] == "200") {

                    $headers = [
                        'Content-Type' => 'application/pdf',
                    ];

                    return response($arrValue['Body'], 200, $headers);
                }
            }
        } else {
            return "File not found in AWS";
        }

    }
}
