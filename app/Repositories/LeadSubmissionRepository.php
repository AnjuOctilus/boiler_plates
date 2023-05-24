<?php

namespace App\Repositories;

use App\Models\BlacklistedItem;
use App\Models\LeadDoc;
use App\Models\Signature;
use Carbon\Carbon;
use App\Models\SplitUuid;
use App\Models\User;
use App\Models\UserAddressDetails;
use App\Models\UserBankDetail;
use App\Models\UserExtraDetail;
use App\Models\UserMilestoneStats;
use App\Models\Visitor;
use App\Models\UserQuestionnaireAnswers;
use App\Repositories\Interfaces\CommonFunctionsInterface;
use App\Repositories\Interfaces\LeadSubmissionApiInterface;
use App\Repositories\Interfaces\S3SignatureDataIngestionInterface;
use App\Repositories\Interfaces\VisitorInterface;
use DB;

class LeadSubmissionRepository implements LeadSubmissionApiInterface{

    /**
     * LPDataIngestionRepository constructor.
     *
     * @param VisitorInterface $visitorInterface
     * @param PixelFireInterface $pixelFireInterface
     * @param UAInterface $ua_repo
     * @param UserInterface $user_repo
     * @param CommonFunctionsInterface $commonFunctionsInterface
     * @param LogInterface $logInterface
     * @param PDFGenerationInterface $pdf_generation_repo
     */

    public function __construct(VisitorInterface $visitorInterface,S3SignatureDataIngestionInterface $s3SignatureDataIngestionInterface,CommonFunctionsInterface $commonFunctionRepo)
    {
        $this->visitorInterface = $visitorInterface;
        $this->s3SignatureDataIngestionInterface = $s3SignatureDataIngestionInterface;
        $this->commonFunctionRepo = $commonFunctionRepo;
        $this->logRepo = new LogRepository;
        /*$this->pixelFireInterface = $pixelFireInterface;
        $this->ua_repo = $ua_repo;
        $this->user_repo = $user_repo;
        $this->commonFunctionsInterface = $commonFunctionsInterface;
        $this->pdf_generation_repo = $pdf_generation_repo;*/
        // $this->logInterface = new LogRepository();

    }

     /**
     * Common splits
     *
     * @param $data
     * @param $visitorParam
     * @param $currentTime
     * @param $pageName
     * @param $queryString
     * @return mixed
     */
    public function commonSplits($data, $currentTime)
    {
       
        $request = (object)$data;
        $arrParam = array(
            'split_path' => $data['split_path'],
            'affiliate_id' => $data['affiliate_id'],
            'transid' => $data['transid'],
            'device_site_id' => $data['site_flag_id'],
            'scr_resolution' => $data['scr_resolution'],
            'country' => $data['country'],
            'ip_address' => isset($data['ip_address']) ? $data['ip_address'] : '',
            'browser' => $data['browser'],
            'platform' => $data['platform'],
            'site_flag' => $data['site_flag'],
            'aff_id' => $data['aff_id'],
            'aff_sub' => $data['aff_sub'],
            'offer_id' => $data['offer_id'],
            'aff_sub2' => $data['aff_sub2'],
            'aff_sub3' => $data['aff_sub3'],
            'aff_sub4' => $data['aff_sub4'],
            'aff_sub5' => $data['aff_sub5'],
            'campaign' => $data['campaign'],
            'source' => $data['source'],
            'tid' => $data['tid'],
            'pid' => $data['pid'],
            'thr_source' => $data['thr_source'],
            'thr_transid' => $data['thr_transid'],
            'thr_sub1' => $data['thr_sub1'],
            'thr_sub2' => $data['thr_sub2'],
            'thr_sub3' => $data['thr_sub3'],
            'thr_sub4' => $data['thr_sub4'],
            'thr_sub5' => $data['thr_sub5'],
            'thr_sub6' => $data['thr_sub6'],
            'thr_sub7' => $data['thr_sub7'],
            'thr_sub8' => $data['thr_sub8'],
            'thr_sub9' => $data['thr_sub9'],
            'thr_sub10' => $data['thr_sub10'],
            'pixel' => $data['pixel'],
            'tracker' => $data['tracker'],
            'atp_source' => $data['atp_source'],
            'atp_vendor' => $data['atp_vendor'],
            'atp_sub1' => $data['atp_sub1'],
            'atp_sub2' => $data['atp_sub2'],
            'atp_sub3' => $data['atp_sub3'],
            'atp_sub4' => $data['atp_sub4'],
            'atp_sub5' => $data['atp_sub5'],
            'media_vendor' => $data['media_vendor'],
            'ext_var1' => $data['ext_var1'],
            'ext_var2' => $data['ext_var2'],
            'ext_var3' => $data['ext_var3'],
            'ext_var4' => $data['ext_var4'],
            'ext_var5' => $data['ext_var5'],
            'adv_vis_id' => $data['adv_vis_id'],
            'existingdomain' => $data['existingdomain'],
            'domain_name' => $data['domain_name'],
            "referer_site" => $data['referer_site'],
            'adv_page' => $data['adv_page'],
            'redirectDomain' => $data['redirectDomain'],
            'user_agent' => $data['user_agent'],
            'split_uuid' => $visitorParam['uuid'],
        );
       
        $arrParam['file_name'] = "";
        //Temporaray removed
        $visitors = $this->visitorInterface->saveVisitor($arrParam, $currentTime);
        $intVisitorId = $visitors['visitor_id'];
        $tracker_type = $visitors['tracker_type'];
        $flagLPVisit = $this->pixelFireInterface->getPixelFireStatus('LP', $intVisitorId);
        $atplog = '0';
        $adtopiapixel = '';

        $response = '';
        $strResult = '';
        $common_repo = new CommonFunctionsRepository;
        if (isset($request->adv_page_name)) {
            $adv_page = $request->adv_page_name;

            $intADVId = $common_repo->getAdvertorialIdFromName($adv_page, $intSiteFlagId = NULL);
        } else {
            $intADVId = 0;
        }
        if (!$flagLPVisit) {
            if ($tracker_type == 1) {
                $chkArry = array(
                    'tracker_type' => $tracker_type,
                    'tracker' => $data['tracker'],
                    'atp_vendor' => $data['atp_vendor'],
                    'pixel' => $data['pixel'],
                    'pixel_type' => 'LP',
                    'statusupdate' => 'SPLIT',
                    'intVisitorId' => $intVisitorId,
                    'redirecturl' => $data['existingdomain']
                );
                $arrResultDetail = $this->pixelFireInterface->atpPixelFire($chkArry);
                if ($arrResultDetail) {
                    $strResult = $arrResultDetail['result'];
                    $response = $arrResultDetail['result_detail'];
                    $adtopiapixel = $arrResultDetail['adtopiapixel'];
                }
            }
            $this->pixelFireInterface->setPixelFireStatus('LP', $intVisitorId);
            return $intVisitorId;
        }
    }
    public function store($data, $data_query, $params, $currentTime, $pageName, $visitorData)
    {
       
        $query = array();
        parse_str($data_query, $query);

        $request_query = (object)$query;

        $request = (object)array_merge($data, $query);
        $domain_name = $visitorData['domain_name'];
        $params['page'] = $pageName;
        $split_id = SplitInfo::where('split_name', '=', $params['page'])->first();
        $request->split_info_id = (string)$split_id->id;
        //get visitor id
        $visitor = SplitUuid::where(['uuid' => $params['uuid']])->first();
        $intVisitorId = isset($visitor->visitor_id) ? $visitor->visitor_id : null;
        if ($intVisitorId && !empty($intVisitorId)) {
            $request->visitor_id = (string)$intVisitorId;
        } else {

            $request->visitor_id = Self::commonSplits($visitorData, $params, $currentTime, $pageName, $data_query);
        }
        $recordStatus = $this->commonFunctionsInterface->isTestLiveEmail($request->txtEmail);
        $user_exist = User::where('email', '=', $request->txtEmail)->where('telephone', '=', $request->txtPhone)->first();

        if (!$user_exist || $recordStatus == 'TEST') {
            $recordStatus = $this->commonFunctionsInterface->isTestLiveEmail($request->txtEmail);

            $arrResponse = $this->user_repo->storeUser($request, $recordStatus, $currentTime, $domain_name);
           // dd($arrResponse);
            //echo "======================USERARRAY==============";
            //print_r($arrResponse);die();
            //log for visitors parameters
            $strFileContent = '\n----------\n Date: ' . date('Y-m-d H:i:s') . "\n Form Submit - Visitors Parameters : " . json_encode($params) . '  \n';
            $logWrite = $this->logInterface->writeLog('-getvisitorsParameters', $strFileContent);

            if (!$arrResponse) {
                return null;
            }
            
            //Update uuid in users table
            $user = User::find($arrResponse['userId']);
            $user->user_uuid = $params['uuid'];
            $user->save();
            
            $intUserId = $arrResponse['userId'];
           // echo "USERID";
            //print_r($intUserId);die();
            $addToHistory = $this->user_repo->storeHistory($intUserId);
            $this->user_repo->storeQuestionsHistory($intUserId);
            //$this->pdf_generation_repo->generateEngagementPDF($intUserId);
            
        }
        
    }
    public function savePhoneDetails($userId,$data,$currentTime)
    {
        if(isset($userId) && !empty( $userId)){
            $phonedetails = array(
                'phone' => isset($data['txtPhone'])?$data['txtPhone']:'',
                'email_id' => isset($data['txtEmail'])?$data['txtEmail']:'',
                'bo_date' =>$currentTime
            );
        }
        \DB::table('bo_phone_number')->insert($phonedetails);
        return;
    }
     /**
     * Save Visitor Data
     */
    public function saveVisitor($arrParam){
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        $uuid = isset($arrParam['uuid'])?$arrParam:'';
        $arrParam['campaign'] = "";
        $arrParam['tracker'] = "";
        $tracker_type = $this->visitorInterface->defineTrackerType($arrParam);
           //Redefine tracker if tracker value become empty
           if (empty($tracker)) {
               if ($tracker_type == 2) {
                   $tracker = "HO";
               } else if ($tracker_type == 3) {
                   $tracker = "THRIVE";
               } else if (substr($tracker_type, 0, 2) == 'FB') {
                   $tracker = "FB";
               } else if ($tracker_type == 5) {
                   $tracker = "GOOGLE";
               }
           }
           $visitor = SplitUuid::where(['uuid' => $uuid])
                   ->select('visitor_id as id')
                   ->first();
                   $tracker_type = 7;
                   $tracker_unique_id = 0;
                   if(empty($visitor)){
                       $objVisitor = new Visitor;
                       $objVisitor->ip_address = '';
                       $objVisitor->browser_type = '';
                       $objVisitor->country = '';
                       $objVisitor->referer_site = '';
                       $objVisitor->existing_domain ='';
                       $objVisitor->full_reference_url = '';
                       $objVisitor->resolution = '';
                       $objVisitor->device_type = '';
                       $objVisitor->user_agent = '';
                       $objVisitor->created_at = $currentTime;
                       $objVisitor->updated_at = $currentTime;
                       $objVisitor->tracker_master_id = $tracker_type;
                       $objVisitor->tracker_unique_id = $tracker_unique_id;
                       $objVisitor->source = $arrParam['affiliate_id'];
                       $objVisitor->save();
                       $intVisitorId = $objVisitor->id;
                       $this->visitorInterface->savUuid($uuid['uuid'], $intVisitorId);
                       return $objVisitor->id;
                   }
          /* if ($tracker_type == 1) {
               $tracker_unique_id = $pixel;
           } else if ($tracker_type == 2) {
               $tracker_unique_id = $strTransid;
           } else if ($tracker_type == 3) {
               $tracker_unique_id = $thr_transid;
           } else if ($tracker_type == 7) {
               $tracker_unique_id = 0;
           } else {
               $tracker_unique_id = $strTransid;
           }*/
   
   
       }

    public function storeLeadData($data, $uuid)
    {
          
        $arrParam = [];
        $arrParam['uuid'] = $uuid;
        $arrParam['affiliate_id'] = $data['affiliate_id'];
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        $visitor = SplitUuid::where(['uuid' => $uuid])->first();
        $intVisitorId = isset($visitor->visitor_id) ? $visitor->visitor_id : null;
        if ($intVisitorId && !empty($intVisitorId)) {
            $data['visitor_id'] = (string)$intVisitorId;
        } else {
            $data['visitor_id'] = $this->saveVisitor($arrParam);

        }
        
        $strJoinDob = '';
        $strDob = '';
        if (!empty($request->DobDay) && !empty($request->DobDay) && !empty($request->DobYear)) {
            $strmnth = date("m", strtotime($request->DobMonth));
            $strDob = $request->DobYear . '-' . $strmnth . '-' . $request->DobDay;
        }
        if (!empty($request->JointDobDay) && !empty($request->JointDobDay) && !empty($request->JointDobYear)) {
            $strjointmnth = date("m", strtotime($request->JointDobMonth));
            $strJoinDob = $request->JointDobYear . '-' . $strjointmnth . '-' . $request->JointDobDay;
        }
        $user_data = array(
            'visitor_id' => isset($data['visitor_id'])?$data['visitor_id']:0,
            'user_uuid'=>$uuid,
            'title' => isset($data['title'])?$data['title']:'',
            'first_name' => isset($data['first_name'])?$data['first_name']:'',
            'middle_name' => isset($data['middle_name'])?$data['middle_name']:'',
            'last_name' =>isset($data['last_name'])?$data['last_name']:'',
            'email' => isset($data['email'])?$data['email']:'',
            'telephone' => isset($data['phone'])?$data['phone']:'',
            'dob' => isset($data['dob'])?$data['dob']:'',
            'record_status' => isset($data['record_status'])?$data['record_status']:'TEST',
            'recent_visit' => NULL,
            'created_at' => $currentTime
        );
       
        $userId = User::insertGetId($user_data);
        $this->savePhoneDetails($userId, $data,$currentTime);
        $userAddressDetails = $this->storeUserAddressDetails($userId,$data);
        if(isset($data['banks']) && !empty($data['banks'])){

            $this->saveUserBanks($data['banks'],$userId);

        }
        if(isset($data['iva_bankrupt']) && !empty($data['iva_bankrupt'])){
            $questionnaireOptions = DB::table('questionnaire_options')
                    ->where('questionnaire_options.questionnaire_id', '=', 1)
                    ->where('questionnaire_options.default_id', '=', $data['iva_bankrupt'])
                    ->first();
            \DB::table('user_questionnaire_answers')->insert([
                'user_id' => $userId,
                'questionnaire_id' => 1,
                'questionnaire_option_id' => $data['iva_bankrupt']
            ]);

        }
        if(isset($data['joint_policy']) && !empty($data['joint_policy'])){
            $questionnaireOptions = DB::table('questionnaire_options')
                    ->where('questionnaire_options.questionnaire_id', '=', 15)
                    ->where('questionnaire_options.default_id', '=', $data['joint_policy'])
                    ->first();
                \DB::table('user_questionnaire_answers')->insert([
                    'user_id' => $userId,
                    'questionnaire_id' => 15,
                    'questionnaire_option_id' => $data['joint_policy']
                ]);

        }

        //if(isset($data['signature_data']) && !empty($data['signature_data']))
        //$this->s3SignatureDataIngestionInterface->saveSignatuteToS3($data['signature_data'], $uuid,"user");
    }

  
    /**
     * Save UserBanks
     */
    public function saveUserBanks($banks,$userId){
       
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
       
        if(isset($banks) && !empty($banks)){

           
            foreach($banks as $bank){
                $objUserBank = new UserBankDetail();
                $objUserBank->user_id = $userId;
                $objUserBank->bank_id = $bank['bank_id'];
                $objUserBank->is_joint = 1;
                $objUserBank->created_at = $currentTime;
                $objUserBank->updated_at = $currentTime;
                $objUserBank->save();
            }
        }


       return;

    }
    /**
     * Save UserAddress Details
     */
    public function storeUserAddressDetails($userId,$data){
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        if ($userId > 0) {
            $password_tkn = $userId . 'bankrefund';
            do {
                $salt = random_bytes(16);
                $token = hash_pbkdf2("sha1", $password_tkn, $salt, 20000, 10);
            } while (User::where('token', '=', $token)->exists());
            User::whereId($userId)->update(array('token' => $token));

            $user_address_details = array(
                'user_id' => $userId,
                'address_type' => '0',
                'postcode' => isset($data['zip_code'])?$data['zip_code']:'',
                'address_line1' => isset($data['address_line1'])?$data['address_line1']:'',
                'address_line2' => isset($data['address_line2'])?$data['address_line2']:'',
                'address_line3' => isset($data['address_line3'])?$data['address_line3']:'',
                'town' => isset($data['town'])?$data['town']:'',
                'locality' => isset($data['locality'])?$data['locality']:'',
                'county' => isset($data['county'])?$data['county']:'',
                'district' => isset($data['district'])?$data['district']:'',
                'country' => isset($data['country'])?$data['country']:'',
                'address_id' => isset($data['address_id'])?$data['address_id']:'',
                'created_at' => $currentTime,
            );
            $IntUserAddressDetails = UserAddressDetails::insertGetId($user_address_details);
      
            if (isset($data['pevious_post_code']) && is_array($data['pevious_post_code'])) {
                $addressType = 1;
                foreach($data['pevious_post_code'] as $key => $PostCode) {
                    $insertData = [
                        'user_id' => $userId,
                        'address_type' => $addressType,
                        'address_line1' => $data['previous_address_line1'][$key] ?? '',
                        'address_line2' => $data['previous_address_line2'][$key] ?? '',
                        'address_line3' => $data['previous_address_line3'][$key] ?? '',
                        'locality' => $data['previous_address_locality'][$key] ?? '',
                        "county"=> $data['previous_address_county'][$key] ?? '',
                        "country"=> $data['previous_address_country'][$key] ?? '',
                        "district"=> $data['previous_address_district'][$key] ?? '',
                        "postcode"=> $PostCode ?? '',
                        "town"=> $data['previous_address_town'][$key] ?? ''
                    ];

                    $insertDataId =\DB::table('user_address_details')->insert($insertData);
                    $addressType++;

                }
            }
          

        }
        return;
    }
    public function saveSignatuteToS3($signatureData, $user_uuid, $sign_holder)
    {
        $signHolder   = ($sign_holder == 'user') ? 'user_' : 'partner_';
        $signFileName = $signHolder.$user_uuid.'.xml';
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

        $s3_path = $this->s3SignatureDataIngestionInterface->saveFileDirectIntoS3($xmlFileName, $xmlFileData, $s3_basic_path);
        return $s3_path;
    }
    public function covertSignToXml($signatureData)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ClientDetails></ClientDetails>');
        //$covertedFile = base64_encode(file_get_contents($signatureData));
       // $covertedFile = base64_encode($signatureData);

        $xml->addAttribute('version', '1.0');
        $xml->addChild('signature', $signatureData);

        // $xmlFile = $filename;
        // $xml->saveXML($xmlFile);
        $xml_content = $xml->asXML();

        return $xml_content;
    }

    /**
     * Save Questions
     */
    public function storeQuestion($data,$uuid){      
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        $user = User::where(['user_uuid' => $uuid])->first();
        foreach($data['question_answers'] as $key=> $questionAnsers){
           
            $buyerResponseData = UserQuestionnaireAnswers::updateOrCreate(
                [   'user_id' => $user->id,
                    'questionnaire_id'=>$key
                ],
                [   "questionnaire_option_id" => $questionAnsers,
                    "status"=>1,
                    "created_at"=>$currentTime,
                    "updated_at"=>$currentTime,
                ]
            );
           

        }
        $objUserExtraDetails = new UserExtraDetail();
        $objUserExtraDetails->user_id = $user->id;
        $objUserExtraDetails->complete_status = 1;
        $objUserExtraDetails->qualify_status = 1;
        $objUserExtraDetails->created_at = $currentTime;
        $objUserExtraDetails->save();
         UserMilestoneStats::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
           ["user_signature" => 1,
            "questions"=>1,
            "completed"=>1,
            "source"=>'live',
            "user_completed"=>1,
            "user_completed_date"=>$currentTime,
            "completed_date"=>$currentTime,
            "created_at"=>$currentTime,
            "updated_at"=>$currentTime
            ]
        );
        return true;
    }
  
    public function saveUserBanksAPI($banks,$userId,$isJoint){
        // dd($banks);
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        // $banks = str_replace(array('[',']'),'',$banks);
        if(isset($banks) && !empty($banks)){

            // $bankArray = is_array($banks) ? $banks : explode(',',$banks);
            foreach($banks as $key => $value){
                $objUserBank = new UserBankDetail();
                $objUserBank->user_id = $userId;
                $objUserBank->bank_id = $value['bank_id'];
                $objUserBank->claim_id = $value['claim_id'];
                $objUserBank->is_joint = $isJoint;
                $objUserBank->created_at = $currentTime;
                $objUserBank->updated_at = $currentTime;
                $objUserBank->save();
            }
        }


       return;

    }
    public function SignatureStore($data,$uuid){
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        //$user = User::where(['user_uuid' => $uuid])->first();
        $visitor = SplitUuid::where(['uuid' => $uuid])->first();
        $visitorId = isset($visitor->visitor_id) ? $visitor->visitor_id:null;
        $user = (isset($visitorId) && !empty($visitorId)) ? User::where(['visitor_id' => $visitorId])->first() :null;
        if(isset($data['signature_data']) && !empty($data['signature_data'])){
        $s3Path = $this->saveSignatuteToS3($data['signature_data'], $uuid,"user");
        $objSignature = new Signature();
        $objSignature->user_id = $user->id;
        $objSignature->bank_id = 0;
        $objSignature->type = 'digital';
        $objSignature->s3_file_path = $s3Path;
        $objSignature->save();
        UserMilestoneStats::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
           ["user_signature" => 1,
            "source"=>'live',
            "user_completed_date"=>$currentTime,
            "completed_date"=>$currentTime,
            "created_at"=>$currentTime,
            "updated_at"=>$currentTime
            ]
        );
        }
       
        return;

    }

    
}
