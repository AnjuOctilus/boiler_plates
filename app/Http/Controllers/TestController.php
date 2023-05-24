<?php


namespace App\Http\Controllers;

use App\Repositories\FollowupRepository;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\LogInterface;
use App\Repositories\Interfaces\PDFGenerationInterface;
use DB;
use App\Jobs\PostLeadsToCake;
use App\Models\LeadDoc;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Class TestController
 *
 * @package App\Http\Controllers
 */
class TestController extends Controller
{
    /**
     * TestController constructor.
     *
     * @param PDFGenerationInterface $pdfinterface
     * @param LogInterface $logRepo
    */
    public function __construct(PDFGenerationInterface $pdfinterface,LogInterface $logRepo)
    {
        $this->pdfs   = $pdfinterface;
        $this->logInterface = $logRepo;
    }
    /**
     * Get pdf
     *
     * @param $id
     * @return mixed
     */
    public function getPDF($id)
    {
        $recordStatus = "TEST";
        $data = $id;
        return $this
             ->pdfs
             ->generatePDF($id);
    }
    /**
     * Send sms
     *
     * @param $userId
     */
    public function sendSMS($user_id){
      $smsObject = new FollowupRepository();
      $smsObject->setFollowupSMSStrategy($user_id);
    }
    /**
     * Send Email
     */
    public function sendEmail($user_id){
        //
    }
    /**
     * Sample log creation
     *
     */
    public function sampleLogCreation()
    {
        $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n Test Log Creation \n";
        $logWrite = $this->logInterface->writeLog('-test_test_log_s311', $strFileContent);
        echo "here";
    }
    /**
     * Sameple s3 log creation
     *
     */
    public function sampleS3LogCreation()
    {
        $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n Test Log Creation \n";
        $logWrite = $this->logInterface->writeLogIntoS3('-test_test_log', $strFileContent);
        echo "here";
    }
    /**
     * Get buyer details
     *
    */
    public function getBuyerDetails()
    {

        echo "---------------------Second Url------------------------------------ \r\n";
        $url1 = 'https://celsolicitors.acdesktop.uk:8443/wsa/wsa1?wsdl';
            $ch1 = curl_init();
            curl_setopt($ch1, CURLOPT_URL, $url1);
            curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
            $response1 = curl_exec($ch1);
            curl_close($ch1);
            print_r($response1);
        echo "---------------------First Url------------------------------------";
        $url = 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        print_r($response);
    }
    /**
     * Buyer post
     *
     * @param $user_id
     */
    public function buyerPost($user_id)
    {
        // dd($user_id);
    }
    /**
     * Check service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkService(){
        $serviceResponse = array('status' => 'Active', 'response' => '200');
        return response()->json($serviceResponse);
    }
    /**
     * Adtopia check connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adtopiaCheckConnection(){
        try {
            DB::connection('mysql_atp');
            $serviceResponse = array('status' => 'Active', 'db_connection'=>'Success', 'response' => '200');
            return response()->json($serviceResponse);
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e );
        }
    }
    /**
     * Adtopia check service
     *
    */
    public function adtopiaCheckService(){
        try {
            echo "Connected successfully to: " . DB::connection('mysql_atp')->getDatabaseName();
        } catch (\Exception $e) {
            die("Could not connect to the database. Please check your configuration. error:" . $e );
        }
    }
    /**
     * Fn regenerate pdf test
     *
     */
    public function generatePDFTest()
    {
        return $this->pdfs->generatePDF(2);
    }
    /**
     * Re post cake
     *
     * @param Request $request
     */
    public function repostCake(Request $request)
    {
        echo "=======INSIDE REPOSTCake Function controller==================";
        $user_id            = isset($request->userid) ? $request->userid : '';
        $recordStatus       = isset($request->status) ? $request->status : 'TEST';
        $milestone_status   = isset($request->milestone_status) ? $request->milestone_status : 'live';
        $flEmail            = isset($request->flEmail) ? $request->flEmail : true;
        $user_id = 1;
        $recordStatus       = isset($request->status) ? $request->status : 'TEST';
        $milestone_status = 'test';

        if(!empty($user_id)){
            dispatch(new PostLeadsToCake($user_id, $recordStatus, $milestone_status));
        }
    }
    /**
     * Get crm posting
     *
     * @param $user_id
     */

     public function generateStatementPDF($userId = 136){
       
       // echo "inside statement PDF";die();
        //$userId = 1;
        // dd($userId);

        return $this->pdfs->generateStatementPDF($userId);
        

     }

     public function generatePreviewPDF($userId = 136){
       
        // echo "inside statement PDF";die();
         //$userId = 1;
         return $this->pdfs->generatePreviewPDF($userId);
         
 
      }

      public function generateAuthenticityPDF($userId = 136){
        return $this->pdfs->generateAuthenticityPDF($userId);

      }

      public function generateQuestionnairePDF($userId = 136){
        return $this->pdfs->generateQuestionnairePDF($userId);

      }
      public function generateEngagementPDF($userId = 164){
        
        return $this->pdfs->generateEngagementPDF($userId);

      }

      public function getPDFLinksfromDb($userId = 1){
        $leadDocs = LeadDoc::where('user_id', $userId)->first();
       $coaPDFFiles =  json_decode($leadDocs->bank_loa_pdf_files);
        echo '<pre>';
        print_r($coaPDFFiles);
       //print_r($leadDocs);
        echo '</pre>';
        die();
      }

      public function TestMyArray(){
        $questions = \Illuminate\Support\Facades\DB::table('questionnaires') 
        ->where('questionnaires.id', '>=', 5)       
        ->select('questionnaires.id')->get()->toArray();
        echo '<pre>';
        //print_r($questions);
        echo '</pre>';
        $questions = array_column($questions, 'id');
        $questionArray = implode(',',$questions);
        $questionArray = '['.$questionArray.']';
        //echo $questionArray;
        echo '<pre>';
       // print_r($array);
        echo '</pre>';


        $questionCount = \Illuminate\Support\Facades\DB::table('questionnaires')
            //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
            //->where('questionnaires.user_id', $user_id)
            //->whereNotIn('uqa.questionnaire_id', [11, 12])
            //->whereIn('que.id', [1,2,3,4,5,6,7,8,9,10])
            ->select('questionnaires.id')
            ->get()->count();
            $commonArray = [];
            for($i=0;$i<=$questionCount;$i++){
                array_push($commonArray,$i);
            }

            $commonArray = implode(',',$commonArray);
            $commonArray =  '['.$commonArray.']';
            echo '<pre>';
            print_r($commonArray);
            echo '</pre>';

        
      }

      public function testPDF()
    {
        return $this->pdfs->generateEngagementPDF(228);
       
    }

    public function testcarbonData(){
       echo "before hal an hour".Carbon::now()->subMinutes(30)  ; 
       echo "before two an hour".Carbon::now()->subHour(2)  ; 
    }
  


}
