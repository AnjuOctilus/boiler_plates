<?php

namespace App\Repositories;

use App\Models\UserMilestoneStats;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Visitor;
use App\Models\VisitorsJourney;
use App\Models\VisitorsSlide;
use App\Models\AdtopiaVisitor;
use App\Models\User;
use App\Models\UserExtraDetail;
use App\Models\DomainDetail;
use App\Models\FollowupHistories;
use App\Models\BuyerApiResponse;
use App\Models\BuyerApiResponseDetails;
use App\Models\UserQuestionnaireAnswers;
use App\Repositories\QuestionnairesRepository;
use App\Repositories\Interfaces\UserInterface;
use App\Repositories\VisitorRepository;
use App\Repositories\LogRepository;
use App\Models\PostcodeLookupResult;
use App\Repositories\HistoryRepository;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\PixelFireRepository;
use DB;
use App\Repositories\ValidationRepository;
use Illuminate\Support\Facades\URL;
use Webpatser\Uuid\Uuid;
use App\Repositories\Interfaces\LogInterface;
use App\Repositories\Interfaces\HistoryInterface;
use App\Repositories\Interfaces\LiveSessionInterface;
use App\Models\UserAddressDetails;
use App\Models\UserBankDetail;
use App\Jobs\SendSMSJob;
use App\Jobs\SendEmailJob;

/**
 * Class UserRepository
 *
 * @package App\Repositories
 */
class UserRepository implements UserInterface
{
    /**
     * UserRepository constructor.
     */
    public function __construct()
    {
        $this->pixelfireRepo = new PixelFireRepository;
        $this->validationRepo = new ValidationRepository;
        $this->visitorRepo = new VisitorRepository;
        $this->logInterface = new LogRepository();
        $this->historyInterface = new HistoryRepository();
        $this->liveSessionInterface = new LiveSessionRepository();
    }
    /**
     * Update user timestamp
     *
     * @param $userId
     */
    public function updateUserTimestamp($userId)
    {
        $user = User::find($userId);
        $user->touch();
    }
    /**
     * Is user complete
     *
     * @param $userId
     * @return int
     */
    public function isUserComplete($userId)
    {
        $time_now = Carbon::now();
        $questionnaire = $this->isQuestionnaireComplete($userId);
        $userSignature = $this->isUserSignComplete($userId);
        $userComplete = DB::table("users as user")
            ->leftJoin('user_extra_details as ue', 'ue.user_id', '=', 'user.id')
            ->leftJoin('user_address_details as uad', 'uad.user_id', '=', 'user.id')
            ->select('user.title as title', 'user.first_name as first_name', 'user.last_name as last_name', 'ue.gender as gender', 'user.dob as dob', 'user.email as email', 'user.telephone as phone', 'uad.country as country', 'uad.postcode as postcode',)
            ->where('user.id', '=', $userId)
            ->distinct('user.id')
            ->first();
        $basicDetails = 0;
        $questionnaireDetails = 0;
        $signatureDetails = 0;
        if (!empty($userComplete->first_name) && !empty($userComplete->last_name) && !empty($userComplete->email) && !empty($userComplete->phone) && !empty($userComplete->postcode) && !empty($userComplete->dob)) {
            $basicDetails = 1;
        }
        if ($questionnaire == 1) {
            $questionnaireDetails = 1;
        }
        if($userSignature  == 1){
            $signatureDetails  = 1;
        }
        // echo "basicDetails = " . $basicDetails;
        // echo "questionnaireDetails = " . $questionnaireDetails;
        if ($basicDetails == 1 && $questionnaireDetails == 1 && $signatureDetails == 1) {
            $update = UserMilestoneStats::where(['user_id' => $userId]);
            $update->update(
                    [
                        'user_completed' => 1,
                        'user_completed_date' => $time_now,
                    ]
                );
            UserExtraDetail::where('user_id', $userId)->update(['complete_status' => 1]);
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Is quesitonaire complete
     *
     * @param $userId
     * @return int
     */
    public function isQuestionnaireComplete($userId)
    {
        $answerCount = DB::table('questionnaires AS Q')
            ->leftJoin('user_questionnaire_answers AS UQA', 'Q.id', '=', 'questionnaire_id')
            ->where('UQA.user_id', '=', $userId)
            ->select('UQA.questionnaire_id')
            ->whereIn('UQA.questionnaire_id', [5, 6, 7, 8, 9, 10, 11, 12, 13, 14])
            ->groupBy('UQA.questionnaire_id')
            ->get()
            ->toArray();
        
        if (sizeof($answerCount) >= 9) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Is user sign complete
     *
     * @param $userId
     * @return int
     */
    public function isUserSignComplete($userId)
    {
        $signComplete = DB::table("users as user")
            ->leftJoin('signatures as s', 'user.id', '=', 's.user_id')
            ->select('s.s3_file_path as userSignature')
            ->where('user.id', '=', $userId)
            ->distinct('user.id')
            ->first();
        $signDetails = 0;

        if (!empty($signComplete->userSignature)) {
            $signDetails = 1;
        }

        if ($signDetails == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Is pdf doc complete
     *
     * @param $userId
     * @return int
     */
    public function isPdfDocComplete($userId)
    {
        $QuestionnairesRepository = new QuestionnairesRepository;
        $questionnaire = $QuestionnairesRepository->isQuestionnaireComplete($userId);
        $pdfDocComplete = DB::table("users as user")
            ->join('lead_docs as ld', 'ld.user_id', '=', 'user.id')
            ->select('ld.user_identification_type as id_type', 'ld.user_identification_image_s3 as id_url', 'ld.spouses_identification_type as p_id_type', 'ld.spouses_identification_image_s3 as p_id_url', 'ld.terms_file as terms_file', 'ld.cover_page as cover_page', 'ld.pdf_file as pdf_file')
            ->where('user.id', '=', $userId)
            ->distinct('user.id')
            ->first();
        $pdfFileDetails = 0;
        $pdfTaxPayerDetails = 0;
        if (!empty($pdfDocComplete->terms_file) && !empty($pdfDocComplete->cover_page) && !empty($pdfDocComplete->pdf_file)) {
            $pdfFileDetails = 1;
        }
        $taxpayer = $this->getTaxPayer($userId);
        if ($taxpayer == 'me') {
            if (!empty($pdfDocComplete->p_id_type) && !empty($pdfDocComplete->p_id_url)) {
                $pdfTaxPayerDetails = 1;
            }
        } elseif ($taxpayer == 'partner') {
            if (!empty($pdfDocComplete->id_type) && !empty($pdfDocComplete->id_url)) {
                $pdfTaxPayerDetails = 1;
            }
        }
        if ($pdfFileDetails == 1 && $pdfTaxPayerDetails == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Is qualified
     *
     * @param $userId
     * @return int
     */
    public function isQualified($userId)
    {
        $userQualified = DB::table("users as user")
            ->leftJoin('user_extra_details as ue', 'ue.user_id', '=', 'user.id')
            ->where('user.id', '=', $userId)
            ->whereIn('user.is_qualified', array(1, 2))
            ->where('ue.qualify_status', 1)
            ->distinct('user.id')
            ->count();
        if ($userQualified == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    public function getVisitorUserTransDetails($intVisitorId, $intUserId, $sqlField = '')
    {
        $recSet = DB::table('visitors AS V')
            ->leftJoin('users AS U', 'V.id', '=', 'U.visitor_id')
            ->leftJoin('user_extra_details', 'U.id', '=', 'user_extra_details.user_id')
            ->leftJoin('buyer_api_responses', 'U.id', '=', 'buyer_api_responses.user_id')
            ->leftJoin('buyer_api_response_details AS BD', 'BD.buyer_api_response_id', '=', 'buyer_api_responses.id');
        if ($sqlField == '') {
            $recSet->select(
                'b.id AS bank_id',
                'V.ip_address',
                'V.campaign',
                'V.tracker_master_id',
                'V.sub_tracker',
                'U.created_at',
                'U.title',
                'U.first_name',
                'U.last_name',
                'U.email',
                'U.telephone',
                'U.dob',
                'buyer_api_responses.api_response',
                'buyer_api_responses.result',
                'U.record_status',
                'BD.lead_value',
                'buyer_api_responses.lead_id',
                'V.adv_visitor_id',
                'V.pid',
                'V.adv_redirect_domain',
                'buyer_api_responses.buyer_id',
                DB::raw("(" . date("Y") . " - YEAR(STR_TO_DATE(U.dob, '%d/%m/%Y'))) AS dobYearDiff")
            );
        } else {
            $recSet->leftJoin('thrive_visitors AS TV', 'V.id', '=', 'TV.visitor_id')
                ->leftJoin('adtopia_visitors AS AV', 'V.id', '=', 'AV.visitor_id')
                ->leftJoin('user_extra_details AS UD', 'U.id', '=', 'UD.user_id')
                ->leftJoin('ho_cake_visitors AS HV', 'V.id', '=', 'HV.visitor_id')
                ->select();
        }
        $arrData = $recSet->where('V.id', '=', $intVisitorId)
            ->where('U.id', '=', $intUserId)
            ->first($sqlField);
        if (!empty($arrData)) {
            return $arrData;
        } else {
            return $arrData = array();
        }
    }
    /**
     * Insert into user
     *
     * @param $intVisitorId
     * @param $arrData
     * @param $currentTime
     * @return int
     * @throws \Exception
     */
    public function insertIntoUser($intVisitorId, $arrData, $currentTime)
    {
        $arrData['response_result'] = (isset($arrData['response_result']) ? serialize($arrData['response_result']) : '');
        $arrData['address_id'] = isset($arrData['address_id']) ? $arrData['address_id'] : '';
        $domain_id = null;
        $domain_name = $arrData['domain'];
        $domain_result = DomainDetail::where('domain_name', '=', $domain_name)
            ->select('id')
            ->first();
        if (!empty($domain_result)) {
            $domain_id = $domain_result->id;
        } else {
            $date = date('Y-m-d H:i:s');
            $domain_data = array(
                'domain_name' => $domain_name,
                'status' => '1',
                'type' => 'LP',
                'last_active_date' => $date
            );
            $domain_id = DomainDetail::insertGetId($domain_data);
        }
        //Insert into db User  table
        $user_data = array(
            'visitor_id' => $intVisitorId,
            'title' => @$arrData['title'],
            'first_name' => @$arrData['fname'],
            'middle_name' => @$arrData['mname'],
            'last_name' => @$arrData['lname'],
            'email' => @$arrData['email'],
            'telephone' => @$arrData['telephone'],
            'dob' => @$arrData['dob'],
            'adv_vis_id' => @$arrData['adv_visitor_id'],
            'domain_id' => @$domain_id,
            'record_status' => @$arrData['record_status'],
            'response_result' => @$arrData['response_result'],
            'recent_visit' => NULL,
            'created_at' => $currentTime
        );
        $intUserId = User::insertGetId($user_data);
        if ($intUserId > 0) {
            $password_tkn = $intUserId . 'bankrefund';
            do {
                $salt = random_bytes(16);
                $token = hash_pbkdf2("sha1", $password_tkn, $salt, 20000, 10);
            } while (User::where('token', '=', $token)->exists());
            User::whereId($intUserId)->update(array('token' => $token));
            //Insert into db  user_details table
            $postCode = strtoupper(str_replace(' ', '', $arrData['postcode']));
            $user_details_data = array(
                'user_id'               => $intUserId,
                'gender'                => isset($arrData['gender'])?$arrData['gender']:'',
                'qualify_status'        => 1,
                'created_at'            => $currentTime,
            );

            $intUserDetailsId = UserExtraDetail::insertGetId($user_details_data);
           
            $user_address_details = array(
                'user_id' => $intUserId,
                'address_type' => '0',
                'postcode' => $postCode,
                'address_line1' => $arrData['txtAddress1'],
                'address_line2' => $arrData['txtAddress2'],
                'address_line3' => $arrData['txtAddress3'],
                'address_line4' => $arrData['txtAddress4'],
                'town' => $arrData['town'],
                'locality' => $arrData['locality'],
                'county' => $arrData['county'],
                'district' => $arrData['district'],
                'country' => $arrData['country'],
                'vendor' => 'getaddress',
                'address_id' => $arrData['addressid'],
                'previous_address' =>$arrData['address'],
                'is_manual' => $arrData['postcode_entry'],
                'created_at' => $currentTime,
            );
// dd($arrData);
            
            $IntUserAddressDetails           = UserAddressDetails::insertGetId($user_address_details);
            // Update user id into pixel firing log table
            VisitorsJourney::where('visitor_id', '=', $intVisitorId)
                ->whereNull('user_id')
                ->update(array('user_id' => $intUserId));
            return $intUserId;
        } else {
            // Write the contents back to the file
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n intVisitorId : $intVisitorId \n";
            //Function call for write log
            //MAIN::writeLog("insert_into_user_failed", $strFileContent);
            $logRepo = new LogRepository;
            $logWrite = $this->logInterface->writeLog('-insert_into_user_failed', $strFileContent);

            return 0;
        }
    }
    /**
     * Insert buyer api response
     *
     * @param $intUserId
     * @param $arrData
     * @return int
     */
    public function insertBuyerApiResponse($intUserId, $arrData)
    {
        $user_trans_data = array(
            'user_id' => $intUserId,
            'buyer_id' => $arrData['leadBuyerId'],
            'lead_id' => $arrData['leadId'],
            'result' => $arrData['result'],
            'api_response' => $arrData['postingResponse'],
        );
        $intBuyerApiResponseId = BuyerApiResponse::insertGetId($user_trans_data);
        if ($intBuyerApiResponseId > 0) {
            return $intBuyerApiResponseId;
        } else {
            // Write the contents back to the file
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n intUserId : $intUserId  \n ";
            $logRepo = new LogRepository;
            $logWrite = $this->logInterface->writeLog('-insert_into_usertrans_failed', $strFileContent);
            return 0;
        }
    }
    /**
     * Insert buyer api  response details
     *
     * @param $buyer_api_response_id
     * @param $arrData
     * @return int
     */
    public function insertBuyerApiResponseDetails($buyer_api_response_id, $arrData)
    {
        $data = array(
            'buyer_api_response_id' => $buyer_api_response_id,
            'lead_value' => @$arrData['leadValue'],
            'post_param' => $arrData['postingParam'],
        );
        $intBuyerApiResponseDetailsId = BuyerApiResponseDetails::insertGetId($data);
        if ($intBuyerApiResponseDetailsId > 0) {
            return $intBuyerApiResponseDetailsId;
        } else {
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n intUserId : \n";
            $logRepo = new LogRepository;
            $logWrite = $this->logInterface->writeLog('-insert_into_usertrans_failed', $strFileContent);

            return 0;
        }
    }
    /**
     * Get lead id
     *
     * @param $intUserId
     * @return mixed
     */
    public function getLeadId($intUserId)
    {
        $lead = BuyerApiResponse::where('user_id', $intUserId)->first();
        if (isset($lead)) {
            return $lead->lead_id;
        }
    }
    /**
     * Store user
     *
     * @param $request
     * @param $recordStatus
     * @param $currentTime
     * @param $domain_name
     * @return array
     * @throws \Exception
     */
    public function storeUser($request, $recordStatus, $currentTime, $domain_name)
    {

        // dd($request);
        $strJoinDob = '';
        $strDob = '';
        $strDom = '';
        $sp_strDob = '';
        if (!empty($request->DobDay) && !empty($request->DobDay) && !empty($request->DobYear)) {
            $strmnth = date("m", strtotime($request->DobMonth));
            $strDob = $request->DobYear . '-' . $strmnth . '-' . $request->DobDay;
        }
        if (!empty($request->JointDobDay) && !empty($request->JointDobDay) && !empty($request->JointDobYear)) {
            $strjointmnth = date("m", strtotime($request->JointDobMonth));
            $strJoinDob = $request->JointDobYear . '-' . $strjointmnth . '-' . $request->JointDobDay;
        }
        $intVisitorId = trim($request->visitor_id);
        $strPostcode = strtoupper($request->txtPostCode);
        $unique_key = 'HLCC_' . $intVisitorId;
        $strFname = @trim($request->txtFName);
        $strMname = @trim($request->txtMName);
        $strLname = @trim($request->txtLName);
        $strEmail = @trim($request->txtEmail);
        $street = @trim($request->txtStreet);
        $housename = @trim($request->txtHouseName);
        $udprn = @trim($request->txtUdprn);
        $pz_mailsort = @trim($request->txtPz_mailsort);

        $txtAddress1 = @trim($request->txtHouseNumber);
        $txtAddress2 = @trim($request->txtAddress2);
        $txtAddress3 = @trim($request->txtStreet);
        $txtAddress4 = @trim($request->txtAddress4);
        $locality    = @trim($request->txtLocality);
        $district    = @trim($request->txtDistrict);
        $county      = @trim($request->txtCounty);
        $town        = @trim($request->txtTown);
        $country     = @trim($request->txtCountry);

        $title = @trim($request->lstSalutation);
        $spouses_title = @trim($request->joint_lstSalutation);
        $user_bank_id = @$request->bank;
        // dd($user_bank);
        $strJointFname = @trim($request->jointFName);
        $strJointLname = @trim($request->jointLName);
        $strqst3 = @$request->question_3;
        $commonFunctionRepo = new CommonFunctionsRepository;
        $postCodeEntry      = @trim($request->pCode_manual);
        $arrData = array(
            'visitor_id' => $intVisitorId,
            'title' => $title,
            'bank' => $user_bank_id,
            'ip_address' => $commonFunctionRepo->get_client_ip(),
            'split_info_id' => $request->split_info_id,
            'fname' => trim($strFname),
            'mname' => trim($strMname),
            'lname' => trim($strLname),
            'dob' => @$strDob,
            'joint_fname' => @$strJointFname,
            'joint_lname' => @$strJointLname,
            'joint_dob' => @$strJoinDob,
            'spouses_title' => $spouses_title,
            'question_3' => @$strqst3,
            'gender' => isset($request->gender) ? $request->gender : '',
            'dom' => @$strDom,
            'telephone' => @trim($request->txtPhone),
            'email' => @trim($strEmail),
            'postcode' => @trim($strPostcode),
            'housenumber' => @trim($request->txtHouseNumber),
            'street' => @$street,
            'housename' => @$housename,
            'address3' => @$street,

            'txtAddress1' => @$txtAddress1,
            'txtAddress2' => @$txtAddress2,
            'txtAddress3' => @$street,
            'txtAddress4' => @$txtAddress4,
            'locality'    => @$locality,
            'county'      => @$county,
            'town'        => @$town,
            'district'    => @$district,
            'country'     => @$country,

            'udprn' => @$udprn,
            'pz_mailsort' => @$pz_mailsort,
            'recent_visit' => @trim($request->strFileName),
            'countryCode' => @$request->countryCode,
            'response_result' => @$request->response_result,
            'address1' => @$request->address1,
            'addressid' => @$request->address1,
            'address' => @$request->address,
            'record_status' => @$recordStatus,
            'unique_key' => @$unique_key,
            'signature' => isset($request->signature) ? $request->signature : '',
            'postcode_entry' =>@$postCodeEntry
        );

        // Declare all variables from arrData
        $intVisitorId = @$arrData['visitor_id'];
        $unique_key = 'HLCC_' . $intVisitorId;
        $strFname = @$arrData['fname'];
        $strMname = @$arrData['mname'];
        $strLname = @$arrData['lname'];
        $split_info_id = (isset($arrData['split_info_id'])) ? $arrData['split_info_id'] : null;
        $strDob = @$arrData['dob'];

        $strPostcode = str_replace(' ', '', $arrData['postcode']);
        $strHouseNumberName = @$arrData['housenumber'];
        $strAddress = @$arrData['address'];
        $strstreet = @$arrData['street'];
        $housename = @$arrData['housename'];

        $address1 = @$arrData['txtAddress1'];
        $address2 = @$arrData['txtAddress2'];
        $address3 = @$arrData['txtAddress3'];
        $address4 = @$arrData['txtAddress4'];
        $strTown = @$arrData['town'];
        $strDistrict = @$arrData['district'];
        $strCounty = @$arrData['county'];
        $country = @$arrData['country'];

        $udprn = @$arrData['udprn'];
        $pz_mailsort = @$arrData['pz_mailsort'];
        $strTelephone = @$arrData['telephone'];
        $strMobile = @$arrData['mobile'];
        $strEmail = @$arrData['email'];
        $strFileName = @$arrData['recent_visit'];
        $countryCode = @$arrData['countryCode'];
        $strRecordStatus = @$arrData['response_result'];
        $strLeadBuyer = @$arrData['lead_buyer'];
        $intLeadBuyerId = @$arrData['lead_buyer_id'];
        $strIpAddres = @$arrData['ip_address'];
        $strDomainName = $domain_name;
        $arrData['unique_key'] = @$unique_key;
        $arrData['domain'] = $strDomainName;
        $AddressID = @$arrData['address1'];
        ################# Get AddressID ####################
        $intAddressId = "";
        $ArrAddid = PostcodeLookupResult::where('visitor_id', '=', $intVisitorId)
            ->select('paf_id')
            ->first();
        if (!empty($ArrAddid)) {
            $intAddressId = $ArrAddid->paf_id;
        }
        $num = '';
        /* Address Lookup Section is updated - 2018/02/27*/
        if ($strAddress == "") {
        }
        $arrVTrans = Visitor::with(['adtopia_visitor', 'thrive_visitor', 'ho_cake_visitor'])->where('id', $intVisitorId)
            ->first();
        if (isset($arrVTrans)) {
            if (isset($arrVTrans->ho_cake_visitor)) {
                $arrData['aff_id'] = $arrVTrans->ho_cake_visitor->aff_id;
                $arrData['aff_sub'] = $arrVTrans->ho_cake_visitor->aff_sub;
                $arrData['offer_id'] = $arrVTrans->ho_cake_visitor->offer_id;
            }
            if (isset($arrVTrans->thrive_visitor)) {
                $arrData['thr_sub1'] = $arrVTrans->thrive_visitor->thr_sub1;
                $arrData['thr_source'] = $arrVTrans->thrive_visitor->thr_source;
            }
            if (isset($arrVTrans->adtopia_visitor)) {
                $arrData['atp_source'] = $arrVTrans->adtopia_visitor->atp_source;
                $arrData['atp_vendor'] = $arrVTrans->adtopia_visitor->atp_vendor;
                $arrData['atp_sub4'] = $arrVTrans->adtopia_visitor->atp_sub4;
                $arrData['atp_sub5'] = $arrVTrans->adtopia_visitor->atp_sub5;
            }
            $arrData['adv_visitor_id'] = $arrVTrans->adv_visitor_id;
            $arrData['sub_tracker'] = $arrVTrans->sub_tracker;
            $arrData['tracker_unique_id'] = $arrVTrans->tracker_unique_id;
            $arrData['tracker_master_id'] = $arrVTrans->tracker_master_id;
            $arrData['tid'] = $arrVTrans->tid;
            $arrData['pid'] = $arrVTrans->pid;
            $arrData['campaign'] = $arrVTrans->campaign;
        } else {
            $arrData['tracker_unique_id'] = "";
            $arrData['aff_id'] = "";
            $arrData['aff_sub'] = "";
            $arrData['offer_id'] = "";
            $arrData['tid'] = "";
            $arrData['pid'] = "";
            $arrData['campaign'] = "";
            $arrData['thr_sub1'] = "";
            $arrData['thr_source'] = "";
            $arrData['sub_tracker'] = "";
            $arrData['atp_source'] = "";
            $arrData['atp_vendor'] = "";
            $arrData['atp_sub4'] = "";
            $arrData['atp_sub5'] = "";
            $arrData['adv_visitor_id'] = 0;
            $arrData['tracker_master_id'] = 7;
        }
        if (!empty($product)) {
            $product_id = $product->id;
        } else {
            $product_id = 0;
        }
        $r = $this->validationRepo->fnUserDuplicateCheck(array("email" => $strEmail, "phone" => $strTelephone, "product_id" => $product_id));
        if ($this->validationRepo->fnUserDuplicateCheck(array("email" => $strEmail, "phone" => $strTelephone, "product_id" => $product_id))) {
            $posttocake = "1";
            $intUserId = "0";
            $strErrorMessage = 'Duplicate Lead Found';
        } else {
            $intUserId = $this->insertIntoUser($intVisitorId, $arrData, $currentTime);
            //signature adding
            /*** Adtopia Pixel Fire ****/
            $visitor_deatil = Visitor::select("tracker_master_id", "sub_tracker", "tracker_unique_id")->whereId($intVisitorId)->first();
            $tracker_type = $visitor_deatil->tracker_master_id;
            $tracker = $visitor_deatil->sub_tracker;
            $currentUrl = URL::full();
            $flagTYVisit = $this->pixelfireRepo->getPixelFireStatus('TY', $intVisitorId);
            if (!$flagTYVisit) {
                if (isset($tracker_type) && $tracker_type == 1) {
                    $pixel = $visitor_deatil->tracker_unique_id;
                    $atp_vendor = AdtopiaVisitor::select("atp_vendor")->whereVisitorId($intVisitorId)->first();
                    $buyer_response = $this->visitorRepo->getVisitorUserTransDetails($intVisitorId, $intUserId, "");
                    $currency = '';
                    $chkArry = array(
                        "tracker_type" => $tracker_type,
                        "tracker" => $tracker,
                        "atp_vendor" => @$atp_vendor->atp_vendor,
                        "pixel" => $pixel,
                        "pixel_type" => "TY",
                        "statusupdate" => "SPLIT",
                        "intVisitorId" => $intVisitorId,
                        "intUserId" => $intUserId,
                        "redirecturl" => $currentUrl,
                        "cakePostStatus" => '',
                        "record_status" => @$buyer_response->record_status,
                        "buyer_id" => @$buyer_response->buyer_id,
                        "revenue" => @$buyer_response->lead_value,
                        "currency" => $currency,
                        "intVoluumtrk2PixelFired" => '',
                        "currentTime"             => $currentTime,
                    );
                    $arrResultDetail = $this->pixelfireRepo->atpPixelFire($chkArry);
                    if ($arrResultDetail) {
                        $strResult = $arrResultDetail['result'];
                        $response = $arrResultDetail['result_detail'];
                        $adtopiapixel = $arrResultDetail['adtopiapixel'];
                    }
                } else {
                    $this->pixelfireRepo->setPixelFireStatus('TY', $intVisitorId, $intUserId);
                }
            }
            /*** Adtopia Pixel Fire ****/
            $arrData['userid'] = $intUserId;
            $posttocake = "";
            if (trim($arrData['town']) == "" || trim($arrData['housenumber']) == "") {
                $num .= "-8888-";
            }
            if (!substr_count($arrData['email'], "@922.com")) {
                // Define ip that need to be block
                $ips = array(
                    "179.43.",
                    "31.132.",
                    "77.75.",
                    "78.157.",
                    "89.47."
                );
                // block users based on blacklisted IPs
                foreach ($ips as $ip) {
                    if (strpos($strIpAddres, $ip) === 0) {
                        $posttocake = "no";
                        $strResult = "Not Posted to CAKE - IP not Valid";
                    }
                }
            }
        }
        
        $strResultMsg = "";
        $sql_select = '';
        //here adding cake posting
        $lead_id = $this->getLeadId($intUserId);
        if ($intUserId > 0) {
            $countryCheck = strtolower($country);
            $qualifiedLead = 2;
            if ($countryCheck === "england" || $countryCheck === "wales") {
                $qualifiedLead = 2;
            } else {
                $qualifiedLead = 0;
            }
            $historyArr = [];
            $questionArray = [];
            $historyArray = [];
            if (isset($request->question_1) && !empty($request->question_1)) {
                $question1 = [
                    'user_id' => $intUserId,
                    'questionnaire_id' => 1,
                    'questionnaire_option_id' => $request->question_1,
                    'input_answer' => null
                ];
                $history1 = [
                    'user_id' => $intUserId,
                    'bank_id' => 0,
                    'type' => 'questionnaire0',
                    'raw_data' => json_encode($question1),
                    'source' => 'live'
                ];
                array_push($questionArray, $question1);
                array_push($historyArray, $history1);
            }
            if (isset($request->question_2) && !empty($request->question_2)) {
                $question2 = [
                    'user_id' => $intUserId,
                    'questionnaire_id' => 2,
                    'questionnaire_option_id' => $request->question_2,
                    'input_answer' => null
                ];
                $history2 = [
                    'user_id' => $intUserId,
                    'bank_id' => 0,
                    'type' => 'questionnaire0',
                    'raw_data' => json_encode($question2),
                    'source' => 'live'
                ];
                array_push($questionArray, $question2);
                array_push($historyArray, $history2);
            }
            if (isset($request->question_3) && !empty($request->question_3)) {
                $question3 = [
                    'user_id' => $intUserId,
                    'questionnaire_id' => 3,
                    'questionnaire_option_id' => $request->question_3,
                    'input_answer' => null
                ];
                $history3 = [
                    'user_id' => $intUserId,
                    'bank_id' => 0,
                    'type' => 'questionnaire0',
                    'raw_data' => json_encode($question3),
                    'source' => 'live'
                ];
                array_push($questionArray, $question3);
                array_push($historyArray, $history3);
            }
            if (isset($request->question_4) && !empty($request->question_4)) {
                $question4 = [
                    'user_id' => $intUserId,
                    'questionnaire_id' => 4,
                    'questionnaire_option_id' => $request->question_4,
                    'input_answer' => null
                ];
                $history4 = [
                    'user_id' => $intUserId,
                    'bank_id' => 0,
                    'type' => 'questionnaire0',
                    'raw_data' => json_encode($question4),
                    'source' => 'live'
                ];
                array_push($questionArray, $question4);
                array_push($historyArray, $history4);
            }
            \Illuminate\Support\Facades\DB::table('user_questionnaire_answers')->insert($questionArray);
            \Illuminate\Support\Facades\DB::table('user_questionnaire_answers_histories')->insert($historyArray);
            //Update is_joint filed
            $isQuestionComplete = $this->isQuestionnaireComplete($intUserId);
            if ($isQuestionComplete == 1) {
                $time_now = Carbon::now();
                $this->liveSessionInterface->createUserMilestoneStats(array(
                    "user_id" => $intUserId,
                    "source" => 'live',
                    "questions" => 1
                ));
            }
            if ($split_info_id != null) {
                $intVisitorSlides = new VisitorsSlide();
                $intVisitorSlides->name = 'slide_info';
                $intVisitorSlides->visitor_id = $intVisitorId;
                $intVisitorSlides->user_id = ($intUserId) ? $intUserId : null;
                $intVisitorSlides->split_id = $split_info_id;
                $intVisitorSlides->save();
                $slide_arr = array(@$slide1, @$slide2, $intVisitorSlides->id);
                VisitorsSlide::whereIn('id', $slide_arr)
                    ->update(array('user_id' => $intUserId));
            }
            
            if (isset($request->bankList) && is_iterable($request->bankList)) {
                // dd($request->bankList);
                foreach($request->bankList as $bankId) {
                    \Illuminate\Support\Facades\DB::table('user_banks')->insert([
                        'bank_id' => $bankId,
                        'user_id' => $intUserId
                    ]);
                }
            }

            if (isset($request->otherBankList) && is_iterable($request->otherBankList)) {
                foreach($request->otherBankList as $bankId) {
                    \Illuminate\Support\Facades\DB::table('user_banks')->insert([
                        'bank_id' => $bankId,
                        'user_id' => $intUserId
                    ]);
                }
            }

            /*$usrExtraTableData = \DB::table('user_extra_details')->where('user_id', $intUserId)->first();
            if ($usrExtraTableData) {
                $extraContent = [
                    'bank_iva' => 'No',
                    'joint_policy' => ucfirst($request->jointly_held_policy)
                ];

                \DB::table('user_extra_details')->where('id', $usrExtraTableData->id)->update($extraContent);
            } else {
                $extraContent = [
                    'bank_iva' => 'No',
                    'joint_policy' => ucfirst($request->jointly_held_policy),
                    'user_id' => $intUserId
                ];

                \DB::table('user_extra_details')->insert($extraContent);
            }*/

            \DB::table('user_questionnaire_answers')->insert([
                'user_id' => $intUserId,
                'questionnaire_id' => 1,
                'questionnaire_option_id' => 2
            ]);

            \DB::table('user_questionnaire_answers')->insert([
                'user_id' => $intUserId,
                'questionnaire_id' => 15,
                'questionnaire_option_id' => (ucfirst($request->jointly_held_policy) == 'Yes' ? 30 : 31)
            ]);

            $strResult = "";
            $strResultMsg = "";
            $posttocake = 1;
            $intResult = ($strResult == 'Success') ? 1 : 0;
            $arrResult = array(
                'result' => $intResult,
                'flag' => $strResult,
                'post_to_cake' => $posttocake,
                'userId' => $intUserId,
                'msg' => $strResultMsg,
            );
            return $arrResult;
        } else {
            $arrUrlParams = array("visitor_id" => $intVisitorId, "user_email" => $arrData['email']);
        }
    }
    /**
     * Store history
     *
     * @param $intUserId
     */
    public function storeHistory($intUserId)
    {
        $getUserDetails = DB::table('users AS U')
            ->leftJoin('user_address_details AS uad', 'U.id', "=", "uad.user_id")
            ->select('U.id', 'U.first_name', 'U.last_name', 'U.email', 'U.telephone', 'U.dob', 'uad.postcode')
            ->where('U.id', '=', $intUserId)
            ->first();
        if (!empty($getUserDetails)) {
            $this->historyInterface->insertFollowupBasicHistory($getUserDetails);
        }
    }
    /**
     * User details
     *
     * @param $userId
     * @return mixed
     */
    public function userDetails($userId)
    {
        $getUserDetails = DB::table('users AS U')
            ->leftJoin('user_extra_details AS UED', 'U.id', "=", "UED.user_id")
            ->leftJoin('user_spouses_details AS USD', 'U.id', "=", "USD.user_id")
            ->leftJoin('signatures AS S', 'S.user_id', '=', 'U.id')
            ->select('U.id', 'U.first_name', 'U.last_name', 'U.email', 'U.telephone', 'U.dob', 'U.is_qualified', 'U.created_at', 'UED.gender', 'UED.housenumber', 'UED.town', 'UED.county', 'UED.country', 'UED.postcode', 'USD.spouses_first_name', 'USD.spouses_last_name', 'USD.dob AS spouses_dob', 'USD.date_of_marriage', 'S.s3_file_path as signature_image',)
            ->where('U.id', '=', $userId)
            ->first();
        return $getUserDetails;
    }

    public function userPdfDetails($userId)
    {
        $getUserDetails = DB::table('users AS U')
            ->leftJoin('user_extra_details AS UED', 'U.id', "=", "UED.user_id")
            ->leftJoin('user_spouses_details AS USD', 'U.id', "=", "USD.user_id")
            ->leftJoin('signatures AS S', 'S.user_id', '=', 'U.id')
            ->select('U.dob AS user_dob','U.*','UED.*','USD.*','S.*')
            ->where('U.id', '=', $userId)
            ->first();

        return (array) $getUserDetails;
    }

    public function userAddressDetails($userId)
    {
        $getUserDetails = DB::table('user_address_details AS UED')
            ->leftJoin('users AS U', "UED.user_id", "=", 'U.id')
            ->where('U.id', '=', $userId)
            ->select("UED.*")
            ->get();
        return $getUserDetails;
    }
    /**
     * User questionnaire answer
     *
     * @param $userId
     * @return mixed
     */
    public function userQuestionnaireAnswers($userId)
    {
        $getUserQuestAnswers = DB::table('user_questionnaire_answers AS UQA')
            ->leftJoin('questionnaires AS Q', 'UQA.questionnaire_id', "=", "Q.id")
            ->leftJoin('questionnaire_options AS QO', 'UQA.questionnaire_option_id', "=", "QO.id")
            ->select('Q.title', 'QO.value', 'Q.id','QO.id AS QO_id')
            ->where('UQA.user_id', '=', $userId)
            ->where('Q.id', '>=', 5)    
            ->where('Q.id', '<=', 14 )
            ->whereNotIn('UQA.questionnaire_id', [1, 15])
            ->get();
        return $getUserQuestAnswers;
    }
    /**
     * Generate UUID
     *
     * @return string
     * @throws \Exception
     */
    public static function GenerateUuid()
    {
        $uuid = Uuid::generate()->string;
        return 'OPC-' . $uuid;
    }
    /**
     * Is pdf doc complete bank
     *
     * @param $userId
     * @return int
     */
    public function isPdfDocCompleteBank($userId)
    {
        $commFunObject = new CommonFunctionsRepository();
        $userBankData = $commFunObject->fetchUserBanks($userId);
        $userLoaPdfData = $commFunObject->fetchUserLoaPdf($userId);
        if (count($userBankData) == count($userLoaPdfData)) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Is user qualified
     *
     * @param $userId
     * @return int
     */
    public function isUserQualified($userId)
    {
        $userQualified = DB::table("users as user")
            ->where('user.id', '=', $userId)
            ->whereIn('user.is_qualified', array(1, 2))
            ->distinct('user.id')
            ->count();
        if ($userQualified == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Store question history
     *
     * @param $userId
     */
    public function storeQuestionsHistory($userId)
    {
        $questions = UserQuestionnaireAnswers::where(['user_id' => $userId])->get();
        foreach ($questions as $each) {
            FollowupHistories::updateOrCreate(
                ['user_id' => $userId, 'source' => 'live', 'type_id' => $each->questionnaire_id],
                [
                    'user_id' => $userId,
                    'bank_id' => null,
                    'type' => 'questionnaire0',
                    'type_id' => $each->questionnaire_id,
                    'value' => ($each->questionnaire_id == 1 || $each->questionnaire_id == 2) ? $each->questionnaire_option_id : $each->input_answer,
                    'post_crm' => 0
                ]
            );
        }
    }
    /**
     * Buyer post allow
     *
     * @param $userId
     * @return string
     */
    public function BuyerPostAllow($userId) // check wether to allow cake posting
    {
        $buyerPostStatus = DB::table('buyer_api_responses AS BAP')
            ->select('BAP.result')
            ->where('BAP.user_id', '=', $userId)
            ->where('BAP.result', '=', "Success")
            ->where('buyer_id', 1)
            ->get();
        $return_val = '';
        if (count($buyerPostStatus) == 0) {
            $return_val = 'true';
        } else {
            $return_val = 'false';
        }
        return $return_val;
    }

    /**
     * Fetch user data
     *
     * @param $user_id
     * @return mixed
     */
    public function fetchUserData($user_id)
    {

        $user_data = DB::table('users as user')
            ->join('user_extra_details as ue', 'ue.user_id', '=', 'user.id')
            ->leftJoin('signatures as s', 'user.id', '=', 's.user_id')
            ->leftjoin('lead_docs as ld', 'ld.user_id', '=', 'user.id')
            ->leftJoin('user_questionnaire_answers as UQA', 'UQA.user_id', '=', 'user.id')
            ->leftJoin('questionnaires as Q', 'Q.id', '=', 'UQA.questionnaire_id')
            ->leftJoin('questionnaire_options as QO', 'QO.id', '=', 'UQA.questionnaire_option_id')
            ->leftJoin('user_banks as ub', 'ub.user_id', '=', 'user.id')
            ->leftjoin('banks', 'banks.id', '=', 'ub.bank_id')
            ->leftJoin('signature_details as sd', 'sd.user_id', '=', 'user.id')
            ->where('user.id', $user_id)
            ->select('user.id as user_id', 'user.user_uuid as user_uuid', 'user.token as token', 'user.title as user_title', 'user.first_name as first_name', 'user.last_name as last_name', 'user.dob as dob', 'user.email as email', 'user.record_status as record_status', 'user.telephone as phone', 'user.is_qualified', 'user.record_status', 'ue.housenumber as house_number', 'ue.housename as house_name', 'ue.address3 as address3', 'ue.addressid as addressid', 'ue.town as town', 'ue.county as county', 'ue.country as country', 'ue.postcode as postcode', 's.id as userSignature', 'ld.pdf_file as pdf_file', 'ld.terms_file as terms_file', 'ld.cover_page as cover_page', 's.previous_name', 'sd.previous_address_line1', 'sd.previous_address_line2', 'sd.previous_address_city', 'sd.previous_address_province', 'sd.previous_address_country', 'sd.previous_postcode', DB::raw('GROUP_CONCAT(Q.id, " ",QO.id, " ",QO.value SEPARATOR " ,") as  questionnaire'), 'ue.street', 'ub.is_joint', 'banks.bank_name', 'ub.bank_sort_code', 'ub.bank_account_number')
            ->groupBy('user.id')
            ->first();
        return $user_data;
    }
    /**
     * Is followup questionnaire complete
     *
     * @param $userId
     * @return int
     */
    public function isFollowupQuestionaireComplete($userId)
    {
        $answerCount = 0;
        $answerResult = DB::table('questionnaires AS Q')
            ->leftJoin('user_questionnaire_answers AS UQA', 'Q.id', '=', 'questionnaire_id')
            ->where('UQA.questionnaire_id', '!=', 1)
            ->where('UQA.user_id', '=', $userId)
            ->get();
        foreach ($answerResult as $each) {
            if ($each->questionnaire_id && $each->input_answer != 'Not in the list') {
                $answerCount++;
            }
        }
        if (@$answerCount >= 3) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Get account type
     *
     * @param null $userId
     * @return false
     */
    public function getUserData($token)
    {
        $users = User::where(['token' => $token])
            ->leftJoin('user_address_details as uad', 'users.id','=', 'uad.user_id')
            ->select(
                'users.id as user_id',
                'users.user_uuid as user_uuid',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.telephone',
                'users.dob as user_dob',
                'uad.postcode',
                'uad.town',
                'uad.county',
                'uad.country',
                'uad.address_line1',
                'uad.address_line2',
            )->get();
        $data = [];
        if (sizeof($users->toArray()) > 0) {
            $data = $users->toArray();
        }
        return $data;
    }

    /**
     * Update user extra complete status
     *
     * @param null $userId
     */
    public function updateUserExtraCompleteStatus($userId = null)
    {
        $user = User::where(['users.id' => $userId])
            ->join('user_address_details as uad', 'users.id', '=', 'uad.user_id')
            ->leftJoin('signatures', 'users.id', 'signatures.user_id')
            ->select('users.first_name', 'users.last_name', 'users.email', 'users.telephone', 'uad.postcode','signatures.s3_file_path as user_sign')
            ->first();
        if ((isset($user->first_name) && !empty($user->first_name))
            && ((isset($user->last_name) && !empty($user->last_name)))
            && ((isset($user->email) && !empty($user->email)))
            && ((isset($user->telephone) && !empty($user->telephone)))
            && ((isset($user->postcode) && !empty($user->postcode)))
            && ((isset($user->user_sign)) && !empty($user->user_sign))
        ) {
            UserExtraDetail::where(['user_id' => $userId])->update(['complete_status' => 1]);
        }
    }
    /**
     * Is qualified bank
     *
     * @param $userId
     * @return int
     */
    public function isQualifiedBank($userId)
    {
        $userQualified = DB::table("users as user")
            ->where('user.id', '=', $userId)
            ->whereIn('user.is_qualified', array(1, 2))
            ->distinct('user.id')
            ->count();
        if ($userQualified == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    public function getAccountType($userId = null)
    {
        $userBank = UserBankDetail::where(['user_id' => $userId])->first();
        return (isset($userBank->is_joint)) ? $userBank->is_joint : false;
    }

    /**
     * Return userdata that has already no entry  followup_sategs table with stage s1
    */
    public function getFollowUpUserDetailsS1(){
       
//Trigger # 1 User Data
       $s1datas = \Illuminate\Support\Facades\DB::table('users') 
        ->join('user_extra_details','users.id','=','user_extra_details.user_id')        
        ->where('users.created_at', '>=', Carbon::now()->subHour(2))  
        ->where('users.created_at', '<=', Carbon::now()->subMinutes(30))    
        ->where('user_extra_details.complete_status', '=', 0)     
        ->select('users.id')
        ->whereNotExists(function($query)
        {
        $query->select(DB::raw(1))
        ->from('followup_stages')
        ->whereRaw('followup_stages.user_id = users.id')
        ->Where('followup_stages.stage', '=', 's1');
       
        })   
        ->get()->toArray();
        $e1Datas = $this->getFollowUpEmailUserDetailsE1();
        $s1UserData =['data'=>$s1datas,'SMSStatus'=>'168'];
        $e1UserData =['data'=>$e1Datas,'malStatus'=>'166']; 
        if($s1UserData['data']){           
            $result =dispatch(new SendSMSJob($s1UserData));
        }
        if($e1UserData['data']){
            $resultEmail = dispatch(new SendEmailJob($e1UserData));
        }
    }
    /**
    * Return userdata that has already no entry  followup_sategs table with stage s2
    */
    public function getFollowUpUserDetailsS2(){ 
//Trigger # 2 User Data

        $s2data = \Illuminate\Support\Facades\DB::table('followup_stages AS a')
        ->join('user_extra_details AS u','a.user_id','=','u.user_id')
        ->join('users AS user','a.user_id', '=', 'user.id')
        ->where('user.created_at', '>=', Carbon::now()->subHour(26))
        ->where('user.created_at', '<=', Carbon::now()->subHour(24))
        ->where('u.complete_status', '=', 0)
        ->where('a.stage', '=', 's1')  
        ->select('a.user_id AS id')
        ->whereNotExists(function($query)
        {
        $query->select(DB::raw(1))
        ->from('followup_stages AS b')
        ->whereRaw('a.user_id = b.user_id ')
        ->Where('b.stage', '=', 's2');
        })
       // ->toSql();
        ->get()->toArray();
        $e2Data = $this->getFollowUpUserDetailsE2();
        $s2UserData = ['data'=>$s2data,'SMSStatus'=>'169'];
        $e2UserData = ['data'=>$e2Data,'malStatus'=>'167'];
        if($s2UserData['data']){           
            $result =dispatch(new SendSMSJob($s2UserData));
          
        }
        if($e2UserData['data']){
            $emailResult = dispatch(new SendEmailJob($e2UserData));
        }


       

    }
    public function getFollowUpUserDetailsS3(){

//Trigger # 3  User Data      
       $s3Data = \Illuminate\Support\Facades\DB::table('followup_stages AS a')
        ->join('user_extra_details AS u','a.user_id','=','u.user_id')
        ->join('users AS user','a.user_id', '=', 'user.id')
        ->where('user.created_at', '>=', Carbon::now()->subHour(48))
        ->where('user.created_at', '<=', Carbon::now()->subHour(46))
        ->where('u.complete_status', '=', 0)
        ->select('a.user_id  AS id')
        ->where('a.stage', '=', 's2')
        ->whereNotExists(function($query)
        {
        $query->select(DB::raw(1))
        ->from('followup_stages AS b')
        ->whereRaw('a.user_id = b.user_id ')
        ->Where('b.stage', '=', 's3');
        })
        //->toSql();
        ->get()->toArray();         
        $user = ['data'=>$s3Data,'SMSStatus'=>'170'];
        if($user['data']){           
            $result =dispatch(new SendSMSJob($user));
          
        }


    }
//Get user Details
    public function getUserDetailsFromUserId($userId){

        $users =  User::where('users.id',$userId)
               ->leftJoin('user_banks', 'users.id', '=', 'user_banks.user_id')
               ->leftJoin('banks', 'user_banks.bank_id', '=', 'banks.id')
               ->select('users.id',
                       'banks.id as bank_id',
                       'banks.bank_code',
                       'banks.bank_name')
               ->where('user_banks.bank_id','!=',null)
             // ->toSql();die();
                ->get();

                   

       return  $users;
       }

       public function getUserBankDetails($userId){

       echo  $userBanks =  \Illuminate\Support\Facades\DB::table('users as user')  
        ->leftJoin('user_banks','user.id', '=', 'user_banks.user_id')
        ->leftJoin('banks', 'user_banks.bank_id', '=', 'banks.id')        
        ->where('user.id', $userId)                
        ->select('user.id', DB::raw("(GROUP_CONCAT(banks.bank_name SEPARATOR ',')) as `banks`"))
        //->toSql();die();
        ->get(); 
        //  dd($userBanks);
       return  $userBanks;
       }


       public function getCompleteUserDetails($userId){
        //echo "complete USER DETAILS INSIDE REPOSITORY".$userId;die();
       $users =  \Illuminate\Support\Facades\DB::table('users as user')  
        ->leftJoin('user_banks','user.id', '=', 'user_banks.user_id')
        ->leftJoin('banks', 'user_banks.bank_id', '=', 'banks.id')  
        ->leftJoin('signatures','user.id','signatures.user_id')      
        ->where('user.id', $userId) 
        ->select('user.id',
                'user.first_name',
                'user.last_name',
                'user.email',
                'user.token',
                'user.telephone',
                'user.dob as user_dob',
                'banks.id as bank_id',
                'banks.bank_code',
                'banks.bank_name',
                'signatures.s3_file_path as signature_image',
                'signatures.created_at as signature_created_at',
                'user_banks.is_joint',
                'user_banks.bank_sort_code as sort_code',
                'user_banks.bank_account_number as account_number'
            )->get()->first();
            //->toSql();
            //->get()->first();
            return $users;
        
       }

       public function getUserQuestionAnswerArray($user_id){
         $data = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')
         ->where('uqa.user_id', $user_id)
         ->select('uqa.questionnaire_option_id','uqa.questionnaire_id')
        // ->toSql();
         ->get()->toArray();
         //print_r($data);die();
         $result = [];
         $resultArray = [];
         $records = [];
         $questionRecords = [];
         foreach ($data as $each){
            //echo $each->questionnaire_id;echo "\n";
           // echo $each->questionnaire_option_id;echo "\n";
           // $resultArray['questions'] = $each->questionnaire_id;
           $resultArray[$each->questionnaire_id]['answers'] = $each->questionnaire_option_id;
            //$questionRecords = array_column($records, $each->questionnaire_id, $each->questionnaire_option_id );
            
           //$resultArray[] = implode(',', array($each->questionnaire_id,$each->questionnaire_option_id));
            
            
        }
        array_push($result,$resultArray);
        return $result[0];

 
     }

     /**
      * Send Email for Pending Users
      */
     public function getFollowUpUserDetailsS1Pending(){
 
        //Trigger # 1 User Data
               $s1Data = \Illuminate\Support\Facades\DB::table('users') 
                ->join('user_extra_details','users.id','=','user_extra_details.user_id')        
                ->where('users.created_at', '>=', '2022-11-23 16:00:03')  
                ->where('users.created_at', '<=', '2022-11-25 13:00:00')    
                ->where('user_extra_details.complete_status', '=', 0)     
                ->select('users.id')
                ->whereNotExists(function($query)
                {
                $query->select(DB::raw(1))
                ->from('followup_stages')
                ->whereRaw('followup_stages.user_id = users.id')
                ->Where('followup_stages.stage', '=', 's1');
               
                })  
                //->toSql(); 
                ->get()->toArray();
        
                
                $e1Data = $this->getFollowUpUserDetailsS1PendingE1();
                $s1PendingUserData =['data'=>$s1Data,'SMSStatus'=>'168'];
                $e1PendingUserData =['data'=>$e1Data,'malStatus'=>'166'];
                                
                if($s1PendingUserData['data']){           
                    $result =dispatch(new SendSMSJob($s1PendingUserData));
                  
                }
                if($e1PendingUserData['data']){           
                    $result =dispatch(new SendEmailJob($e1PendingUserData));
                  
                }
            }

            /**
             * Return userdata that has already no entry  followup_sategs table with stage e1
             */
            public function getFollowUpEmailUserDetailsE1(){

                $e1datas = \Illuminate\Support\Facades\DB::table('users') 
                ->join('user_extra_details','users.id','=','user_extra_details.user_id')        
                ->where('users.created_at', '>=', Carbon::now()->subHour(2))  
                ->where('users.created_at', '<=', Carbon::now()->subMinutes(30))    
                ->where('user_extra_details.complete_status', '=', 0)     
                ->select('users.id')
                ->whereNotExists(function($query)
                {
                $query->select(DB::raw(1))
                ->from('followup_stages')
                ->whereRaw('followup_stages.user_id = users.id')
                ->Where('followup_stages.stage', '=', 'e1');
               
                })   
                ->get()->toArray();
        
                return $e1datas;
                
               
            }

            /**
             * Return userdata that has already no entry  followup_sategs table with stage e2
             */
            public function getFollowUpUserDetailsE2(){
                $e2data = \Illuminate\Support\Facades\DB::table('followup_stages AS a')
                ->join('user_extra_details AS u','a.user_id','=','u.user_id')
                ->join('users AS user','a.user_id', '=', 'user.id')
                ->where('user.created_at', '>=', Carbon::now()->subHour(26))
                ->where('user.created_at', '<=', Carbon::now()->subHour(24))
                ->where('u.complete_status', '=', 0)
                ->where('a.stage', '=', 'e1')  
                ->select('a.user_id AS id')
                ->whereNotExists(function($query)
                {
                $query->select(DB::raw(1))
                ->from('followup_stages AS b')
                ->whereRaw('a.user_id = b.user_id ')
                ->Where('b.stage', '=', 'e2');
                })
                ->get()->toArray();

                return $e2data;
      

            }


            public function getFollowUpUserDetailsS1PendingE1(){
                $e1Data = \Illuminate\Support\Facades\DB::table('users') 
                ->join('user_extra_details','users.id','=','user_extra_details.user_id')        
                ->where('users.created_at', '>=', '2022-11-23 16:00:03')  
                ->where('users.created_at', '<=', '2022-11-25 13:00:00')    
                ->where('user_extra_details.complete_status', '=', 0)     
                ->select('users.id')
                ->whereNotExists(function($query)
                {
                $query->select(DB::raw(1))
                ->from('followup_stages')
                ->whereRaw('followup_stages.user_id = users.id')
                ->Where('followup_stages.stage', '=', 'e1');
               
                })  
                //->toSql(); 
                ->get()->toArray();
                return $e1Data;

            }

       


}
