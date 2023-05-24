<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return "Welcome -Online Plevin Check API End-point";
});

$router->get('/testing-env', function ()  {
    echo env('APP_ENV');
    echo env('DB_DATABASE');
    echo env('REDIS_QUEUE');
    echo "sqs";
    echo env('SQS_KEY');
    echo env('SQS_SECRET');
    echo env('SQS_PREFIX');
    echo env('SQS_QUEUE');
    echo env('SQS_REGION');
    echo "AWS_BUCKET=" . env('AWS_BUCKET') . "<br/>";
    echo "Adtopia Parameters". "<br/>";
    echo env('ATP_DB_CONNECTION'). "<br/>";
    echo env('ATP_DB_HOST_READ'). "<br/>";
    echo env('ATP_DB_HOST_WRITE'). "<br/>";
    echo env('ATP_DB_PORT'). "<br/>";
    echo env('ATP_DB_DATABASE'). "<br/>";
    echo env('ATP_DB_USERNAME'). "<br/>";
    echo env('ATP_DB_PASSWORD'). "<br/>";
    echo "token=".env('ADTOPIA_TOKEN'). "<br/>";
    echo "CRM_AUTH_TOKEN=".env('CRM_AUTH_TOKEN'). "<br/>";
    echo "CRM_URL=".env('CRM_URL'). "<br/>";
});

$router->get('/check-sqs-env', function ()  {
    echo "APP_ENV=" . env('APP_ENV') . "<br/>";
    echo "APP_URL=" . env('APP_URL') . "<br/>";
    echo "AWS_BUCKET=" . env('AWS_BUCKET') . "<br/>";
    echo "AWS_DEFAULT_REGION=" . env('AWS_DEFAULT_REGION') . "<br/>";
    echo "AWS_ACCESS_KEY_ID=" . env('AWS_ACCESS_KEY_ID') . "<br/>";
    echo "AWS_SECRET_ACCESS_KEY=" . env('AWS_SECRET_ACCESS_KEY') . "<br/><br/>";
    echo "QUEUE_CONNECTION=" . env('QUEUE_CONNECTION') . "<br/>";
    echo "SQS_KEY=" . env('SQS_KEY') . "<br/>";
    echo "SQS_SECRET=" . env('SQS_SECRET') . "<br/>";
    echo "SQS_QUEUE=" . env('SQS_QUEUE') . "<br/>";
    echo "SQS_REGION=" . env('SQS_REGION') . "<br/>";
    echo "SQS_PREFIX=" . env('SQS_PREFIX') . "<br/>";
});
$router->get('testcake','V1\UserController@testCake');

$router->group(['prefix' => 'api/v1'], function () use ($router) {
	$router->post('user-agent', 'V1\UserAgentController@getUseragentInfo');
    $router->post('get-uuid', 'V1\UserAgentController@getUUID');
	$router->post('data-ingestion-pipeline','V1\DataIngestionController@pipeline');
	$router->get('/get-phone-validation','V1\ValidationController@getValidPhone');
	$router->get('/get-email-validation','V1\ValidationController@getValidEmail');
    $router->get('/user-info','V1\UserController@getUserInfo');
   
    

    //Cake posting
    $router->post('/buyerresponse','V1\Api\BuyerResponseController@index');
    $router->get('/cake-post/{user_id}','V1\PostCakeController@processCake');
    $router->get('/buyer-post/{user_id}','TestController@buyerPost');
    $router->post('/user-qualification','V1\UserController@getUserQulification');

    //Follow up
    $router->get('/buyer-post/{user_id}','TestController@buyerPost');
    $router->get('/followup/user/list','V1\UserController@getFollowUpUserList');
    $router->get('/followup/get-pending-details','V1\FollowupController@getPendingUserDetails');
    $router->get('/followup/get-uuid','V1\FollowupController@getUuid');
    $router->get('/generate-pdf/{userId}','V1\UserController@generatePdf');

    #api calls
    $router->post('/signature-api','V1\UserController@removeUserSignature');
    $router->post('/pdfremoval-api','V1\UserController@pdfRemovalApi');
    $router->post('/pdfregeneration-api','V1\UserController@pdfRegenerateApi');

    $router->get('/stats','TestController@checkService');   
    $router->post('/adv_contact','V1\ContactController@index'); 

    $router->post('/remove-signature-api','V1\UserController@deleteSignature');
    $router->post('/restore-signature-api','V1\UserController@restoreSignature');
    $router->post('lead-api','V1\UserController@leadSubmissionApi');
    $router->post('generate-pdf-api','V1\UserController@generatePdfApi');
    
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('run-command/{command}/{param}', 'CommandController@index');
    $router->get('/php-info', function ()  {
        phpinfo();
    });
    $router->get('/check-service','TestController@checkService');
    $router->get('/adtopia-check-connection','TestController@adtopiaCheckConnection');
    $router->get('/check-adtopia-service','TestController@adtopiaCheckService');
});

$router->get('/generate-loa-pdf','TestController@generatePDFTest');
$router->get('/generate-test-pdf','TestController@generatePDFTApiTest');
$router->get('/get-buyer','TestController@getBuyerDetails');
$router->get('/get-pdf/{id}','TestController@getPDF');
$router->get('/repost-cake','TestController@repostCake');
//$router->get('/loa_pdfs/{filename}','PDFDownloadController@index');
//$router->get('/coa_pdfs/{filename}','PDFDownloadController@index');

$router->get('/coa_pdfs/{surname}_COA_{lender}_{filename}','PDFDownloadController@index');
$router->get('/loa_pdfs/{surname}_LOA_{lender}_{filename}','PDFDownloadController@index');

$router->get('/questionnaire_pdfs/{surname}_QUE_{filename}','PDFDownloadController@index');
$router->get('/questionnaire_pdfs/Questionnaire_{filename}','PDFDownloadController@index');

$router->get('/preview_pdfs/{surname}_PREV_{filename}','PDFDownloadController@index');
$router->get('/witness_pdfs/{surname}_WITNESS_{filename}','PDFDownloadController@index');

$router->get('/statement_pdfs/{surname}_STAT_{filename}','PDFDownloadController@index');
$router->get('/truth_pdfs/{surname}_TRUTH_{filename}','PDFDownloadController@index');

//$router->get('/questionnaire_pdfs/{filename}','PDFDownloadController@index');
//$router->get('/preview_pdfs/{filename}','PDFDownloadController@index');
//$router->get('/statement_pdfs/{filename}','PDFDownloadController@index');
$router->get('/get-crm-post/{id}','TestController@getCrmPosting');
$router->get('/get-statement-pdf/{id}','TestController@generateStatementPDF');
$router->get('/get-preview-pdf/{id}','TestController@generatePreviewPDF');
$router->get('/generate-authenticity-pdf/{id}','TestController@generateAuthenticityPDF');
$router->get('/generate-questionnaire-pdf/{id}','TestController@generateQuestionnairePDF');
$router->get('/get-user-signature-data','TestController@getUserSignatureData');
$router->get('/generate/engagement/pdf/{id}','TestController@generateEngagementPDF');
$router->get('/test-my-pdf/{id}','TestController@testPDF');
$router->get('/test-get-lead-docs','TestController@getLeadDocsData');
$router->get('/test-date','TestController@testcarbonData');



$router->get('/generate-pdf/{userId}','V1\UserController@generatePdf');

$router->get('/test-address','V1\UserController@getUserAddress');

//car api check
Route::get('/sentry/test', function () {
    throw new Exception('My first Sentry error-check for capital life');
});
//log into s3
$router->get('/samplelog','TestController@sampleLogCreation');
$router->get('/samples3log','TestController@sampleS3LogCreation');
