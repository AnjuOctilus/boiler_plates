<?php
namespace App\Repositories;
use App\Models\BuyerApiResponse;
use App\Models\BuyerApiResponseDetails;
use App\Models\UserQuestionnaireAnswers;
use App\Repositories\LogRepository;
use App\Repositories\Interfaces\CakeInterface;
use App\Repositories\LiveSessionRepository;
use App\Repositories\CommonFunctionsRepository;
use App\Models\BlacklistedItem;
use App\Models\User;
use App\Models\Visitor;
use App\Models\LeadDoc;
use App\Models\UserAddressDetails;
use App\Repositories\UserRepository;
use \Exception;
use DB;
/**
 * Class CakeRepository
 *
 * @package App\Repositories
 */
class CakeRepository implements CakeInterface {
    /**
     * CakeRepository constructor.
     */
    public function __construct() {
        $this->liveSessionRepo = new LiveSessionRepository;
        $this->logRepo = new LogRepository;
        $this->commonFunctionRepo = new CommonFunctionsRepository;
    }
    /**
     * Cake post
     *
     * @param $userId
     * @param $buyyerId
     * @return array
     */
    public function cakePost($userId, $buyyerId = NULL) {
        $userId = intval($userId);
        $user_data = User::join('user_extra_details', 'users.id', '=', 'user_extra_details.user_id')
        ->join('visitors', 'users.visitor_id', '=', 'visitors.id')
        ->join('user_address_details', 'users.id', '=', 'user_address_details.user_id')->where('users.id', $userId)
        ->select('visitors.id as visitor_id', 'visitors.ip_address', 'user_extra_details.addressid', 
        'users.title', 'users.first_name','users.middle_name', 'users.last_name', 'users.dob', 'user_extra_details.postcode', 
        'users.telephone', 'users.email', 'user_extra_details.housenumber', 'user_extra_details.address', 
        'user_extra_details.street', 'user_extra_details.county', 'user_extra_details.town', 
        'user_extra_details.gender', 'user_extra_details.housename', 'user_extra_details.address3', 
        'user_extra_details.udprn', 'user_extra_details.pz_mailsort', 'user_extra_details.country as user_country', 
        'user_extra_details.deliverypointsuffix', 'users.recent_visit', 'visitors.country as countryCode', 
        'users.response_result', 'visitors.user_agent','user_address_details.county as userCounty',
        'user_address_details.country as userCountry','user_address_details.address_line1 as userAddress',
        'user_address_details.address_line2 as UserPrevAddress2','user_address_details.address_line3 as UserPrevAddress3',
        'user_address_details.address_line4 as UserPrevAddress4','user_address_details.town as userTown','user_address_details.locality as userLocality',
        'user_address_details.postcode as userPostCode','user_address_details.previous_name as previousName')->first();
        if (isset($user_data->dob)) {
            $arrDob = explode('-', $user_data->dob);
            $strDate = $arrDob[2];
            $strMonth = $arrDob[1];
            $strBirthYear = $arrDob[0];
        }
        $userAgent = isset($user_data->user_agent) ? $user_data->user_agent : '';
        $split_info = Visitor::join('split_info', 'visitors.split_id', '=', 'split_info.id')->where('visitors.id', $user_data->visitor_id)->select('split_info.split_path', 'split_info.split_name')->first();
        $splitName = '';
        if (isset($split_info)) {
            $splitName = $split_info->split_name;
        }
        $previousAddressDetails = $this->getUserPreviousAddress($userId);
        $telephone = @$user_data->telephone;
        $phone_home = null;
        $mobile = null;
        $unique_key = 'Plevin_' . $user_data->visitor_id;
        $ip_address = @$user_data->ip_address;
        $dob = $user_data->dob;
        $address = $user_data->address;
        $county = $user_data->userCounty;
        $country = $user_data->userCountry;
        $recent_visit = $user_data->recent_visit;
        $countryCode = $user_data->countryCode;
        $response_result = null;
        $address_id = $user_data->addressid;
        $address1 = $user_data->address3;
        $dobday = $strDate;
        $dobmonth = $strMonth;
        $dobyear = $strBirthYear;
        $userid = (String) $userId;

        $arrVTrans = Visitor::with(['adtopia_visitor', 'thrive_visitor', 'ho_cake_visitor'])->where('id', $user_data->visitor_id)->first();
        if (isset($arrVTrans)) {
            if (isset($arrVTrans->ho_cake_visitor)) {
                $aff_id = $arrVTrans->ho_cake_visitor->aff_id;
                $aff_sub = $arrVTrans->ho_cake_visitor->aff_sub;
                $offer_id = $arrVTrans->ho_cake_visitor->offer_id;
            }
            if (isset($arrVTrans->thrive_visitor)) {
                $thr_sub1 = $arrVTrans->thrive_visitor->thr_sub1;
                $thr_source = $arrVTrans->thrive_visitor->thr_source;
            }
            if (isset($arrVTrans->adtopia_visitor)) {
                $atp_source = $arrVTrans->adtopia_visitor->atp_source;
                $atp_vendor = $arrVTrans->adtopia_visitor->atp_vendor;
                $atp_sub1 = $arrVTrans->adtopia_visitor->atp_sub1;
                $atp_sub2 = $arrVTrans->adtopia_visitor->atp_sub2;
                $atp_sub3 = $arrVTrans->adtopia_visitor->atp_sub3;
                $atp_sub4 = $arrVTrans->adtopia_visitor->atp_sub4;
                $atp_sub5 = $arrVTrans->adtopia_visitor->atp_sub5;
            }
            $adv_visitor_id = $arrVTrans->adv_visitor_id;
            $sub_tracker = $arrVTrans->sub_tracker;
            $tracker_unique_id = $arrVTrans->tracker_unique_id;
            $tracker_master_id = $arrVTrans->tracker_master_id;
            $tid = $arrVTrans->tid;
            $pid = $arrVTrans->pid;
            $campaign = $arrVTrans->campaign;
        } else {
            $tracker_unique_id = '';
            $aff_id = '';
            $aff_sub = '';
            $offer_id = '';
            $tid = '';
            $pid = '';
            $campaign = '';
            $thr_sub1 = '';
            $thr_source = '';
            $sub_tracker = '';
            $atp_source = '';
            $atp_vendor = '';
            $arratp_sub4 = '';
            $atp_sub5 = '';
            $adv_visitor_id = 0;
            $tracker_master_id = 7;
        }
        //Fetch bank buyer mapping info
        if (@$aff_id == 0) {
            $aff_id = '';
        }
        if (@$aff_sub == 0) {
            $aff_sub = '';
        }
        if (@$offer_id == 0) {
            $offer_id = '';
        }
        if (substr($tid, 0, 2) == 'HO') {
            $tid = $tid;
        } else {
            $tid = '';
        }
        $lenders = '';
        ## ckm_sub_id #####
        $ckm_subid = $aff_id;
        $ckm_subid_2 = $offer_id;
        $ckm_subid_3 = $tracker_unique_id;
        $ckm_subid_4 = $tid;
        if ($tracker_master_id == '3') {
            $ckm_subid = $thr_sub1;
        } else if ($tracker_master_id == '1') {
            $ckm_subid = $atp_vendor;
            //vendor
            $ckm_subid_2 = $tracker_master_id;
            //tracker
            $ckm_subid_3 = $tracker_unique_id;
            //pixel id
            $ckm_subid_4 = $atp_source;
            //vendor source
            
        }
        # ckm_sub_id #####
        if ($tracker_master_id == '1') {
            $ckm_subid_5 = 'atp##' . $atp_source . '##' . $tracker_unique_id;
        } else if (!empty($sub_tracker)) {
            if ($tracker_master_id == '2') {
                $ckm_subid_5 = $sub_tracker . '##*##' . $tracker_unique_id;
            } else if ($tracker_master_id == '3') {
                $ckm_subid_5 = $sub_tracker . '##' . $tracker_unique_id . '##' . $thr_transid;
            } else if ($tracker_master_id == '4') {
                $ckm_subid_5 = $sub_tracker . '##' . $campaign;
            } else if ($tracker_master_id == '5') {
                $ckm_subid_5 = $sub_tracker . '##' . $campaign;
            } else {
                $ckm_subid_5 = $sub_tracker;
            }
        } else {
            $ckm_subid_5 = 'UNKNOWN';
        }
        ## telephone ##
        $str_fistChar = substr($telephone, 0, 1);
        if ($str_fistChar != 0) {
            $telephone = '0' . @$telephone;
        }
        $str_phone = @$telephone;
        $strTelephone = @$telephone;
        $phone_home = '';
        if ($strTelephone != '') {
            if (substr($strTelephone, 0, 2) != '07') {
                $phone_home = $strTelephone;
            } else {
                $mobile = $strTelephone;
            }
        }
        ## telephone ##
        $Fname_new = ucfirst(strtolower(@$user_data->first_name));
        $Lname_new = ucfirst(strtolower(@$user_data->last_name));
        $userAnswer = UserQuestionnaireAnswers::where(['user_id' => $userId, 'questionnaire_id' => 2])->first();
        if (isset($userAnswer->input_answer) && !empty($userAnswer->input_answer)) {
            $dobyear = $userAnswer->input_answer;
        }
        //  LOA and Pdf
        // pdf Links
        $pdflinks = [];
        $loaId = [];
        $pdfarray = array();
        $coapdfarray = array();
        $coapdflinks = [];
        $witnessStatmentPdfArray = array();
        $witnessStatementLinks = [];
        $pdf_details = LeadDoc::where(['user_id' => $userId])->select('bank_loa_pdf_files', 'pdf_file','witness_statement_pdf','statement_of_truth_pdf','questionnaire_pdf_files')->first();
        //  return gettype($pdf_details);
        //print_r($pdf_details);

        //$pdf_details = null;
        if ($pdf_details) {
            $pdfarray = json_decode($pdf_details->bank_loa_pdf_files, true);
            
            $loaIdCount = 1;
            if (isset($pdfarray) && !empty($pdfarray)) {
                //foreach ($pdfarray as $bankkey => $pdfpath) {
                    foreach($pdfarray as $pdfPaths){
                        if ($loaIdCount <= 10) {
                            $pdflinks[] = urlencode($pdfPaths);
                            $loaId[] = $userid . "-L" . $loaIdCount;
                        }
                        $loaIdCount++;
                }
                //}
            }

            $pdflink_string = implode(",", $pdflinks);
            //COA
            $coapdfarray = json_decode($pdf_details->pdf_file, true);
            $loaIdCount_coa = 1;
            if (isset($coapdfarray) && !empty($coapdfarray)) {
                //foreach ($coapdfarray as $bankkey => $coapdfpath) {
                    foreach($coapdfarray as $coapdfpaths){
                        if ($loaIdCount_coa <= 10) {
                            $coapdflinks[] = urlencode($coapdfpaths);
                        }
                        $loaIdCount_coa++;
                    }
                //}
            }

            $coapdflink_string = implode(",", $coapdflinks);
            $loaIdString = implode(",", $loaId);
            $witnessStatmentPdf = $pdf_details->witness_statement_pdf;
            $statementofTruthPdf = $pdf_details->statement_of_truth_pdf;
            $questionnairePdf = $pdf_details->questionnaire_pdf_files;
          

        } else {
            $leadDocData = \DB::table('lead_docs')->where('user_id', $userId)->get()->toArray();
            throw new \Exception('userId=' . $userId . ': Lead docs data not found' . json_encode($leadDocData));

            $pdflink_string = '';
            $loaIdString = '';
        }
        //   close Loa and PDf
        $total_loans = '';
        $loan_amount = '';
        $loans_after2007 = '';
        $bankruptcy_iva = '';
        $objUserQuestAnswers = $this->userQuestionnaireAnswerss($userId)->toArray();
        if (isset($objUserQuestAnswers['0']) && $objUserQuestAnswers['0']->id == 1) {
            $total_loans = $objUserQuestAnswers['0']->value;
        }
        if (isset($objUserQuestAnswers['1']) && $objUserQuestAnswers['1']->id == 2) {
            $loan_amount = $objUserQuestAnswers['1']->value;
        }
        if (isset($objUserQuestAnswers['2']) && $objUserQuestAnswers['2']->id == 3) {
            $loans_after2007 = $objUserQuestAnswers['2']->value;
        }
        if (isset($objUserQuestAnswers['3']) && $objUserQuestAnswers['3']->id == 4) {
            $bankruptcy_iva = $objUserQuestAnswers['3']->value;
        }
        if (isset($objUserQuestAnswers['0']) && $objUserQuestAnswers['0']->id == 1) {
            $iva = $objUserQuestAnswers['0']->value;
        }
        if (isset($objUserQuestAnswers['11']) && $objUserQuestAnswers['11']->id == 15) {
            $joint_policy = $objUserQuestAnswers['11']->value;
        }
        $userBanDataArray = [];
        $userBankData = $this->commonFunctionRepo->fetchUserBanks($userId);
        foreach ($userBankData as $bankkey => $bankvalue) {
            array_push( $userBanDataArray,$bankvalue->bank_name);
            //$arrSubmitData['lender_' . $i] = $bankvalue->bank_name;
           // $i++;
        }
        $lenders = implode(',',$userBanDataArray);
        $firstName = str_replace('&#039;', '`', str_replace("'", '`', stripslashes(@$user_data->first_name)));
        $middleName = str_replace('&#039;', '`', str_replace("'", '`', stripslashes(@$user_data->middle_name)));
        $lastName = str_replace('&#039;', '`', str_replace("'", '`', stripslashes(@$user_data->last_name)));
        $previousName = str_replace('&#039;', '`', str_replace("'", '`', stripslashes(@$previousAddressDetails->previous_name)));
        $arrSubmitData = array(
        //Personal Detail
        'ckm_subid' => $ckm_subid,
        'ckm_subid_2' => $ckm_subid_2, 
        'ckm_subid_3' => $ckm_subid_3, 
        'ckm_subid_4' => $ckm_subid_4, 
        'ckm_subid_5' => $ckm_subid_5, 
        'first_name'=>ucwords($firstName),
        'last_name' => ucwords($lastName), 
        'postcode' => @$user_data->userPostCode, 
        'housename' => @$user_data->UserPrevAddress2, 
        'housenumber'=>@$user_data->UserPrevAddress1, 
        'address1' => @$user_data->UserPrevAddress1, 
        'address2' => @$user_data->UserPrevAddress2, 
        'address3' => @$user_data->UserPrevAddress3, 
        'city' => @$user_data->userTown, 
        'town' => @$user_data->userTown, 
        'county' => @$user_data->userCounty, 
        'country' => @$user_data->userCountry, 
         'Contry'=>@$user_data->userCountry,
        'dob' => @$dob, 
        'dob_day' => ltrim($dobday, "0"), 
        'dob_month' => ltrim($dobmonth, "0"), 
        'dob_year' => ($dobyear > 0) ? @$dobyear : '', 
        'phone_home' => @$phone_home, 
        'mobile' => @$mobile, 
        'phone' => @$str_phone, 
        'email_address' => @$user_data->email, 
        'total_loans' => $total_loans, 
        'loan_amount' => $loan_amount, 
        'loans_after2007' => $loans_after2007, 
        'bankruptcy_iva' => $bankruptcy_iva, 
        'title' => @$user_data->title, 
        'ip_address' => @$ip_address, 
        'loa_id' => $loaIdString, 
        'loa_url' => $pdflink_string, 
        'coa_url' => $coapdflink_string, 
        'witness_statement'=>$witnessStatmentPdf,
        'statement_of_truth'=>$statementofTruthPdf,
        'questionnaire_pdf'=>$questionnairePdf,
        'campaign_name' => $campaign, 
        'split_id' => @$splitName, 
        'unique_key' => @$unique_key, 
        'userid' => @$userId, 
        'Contry' => @$user_data->user_country, 
        'domain_name' => env('CAKE_API_URL', 'onlineplevincheck.co.uk'), 
        'Fname' => str_replace('&#039;', '`', str_replace("'", '`', stripslashes($Fname_new))), 
        'affiliate_id' => $aff_id, //ad_group
        'aff_sub' => $aff_sub, //keyword
        'transid' => $tracker_unique_id, 
        'Lname' => str_replace('&#039;', '`', str_replace("'", '`', stripslashes($Lname_new))), 
        'dps' => @$user_data->deliverypointsuffix, 
        'msc' => @$user_data->pz_mailsort, 
        'udprn' => @$user_data->udprn, 
        'vender' => @$atp_vendor, 
        'vendor_source' => @$atp_source, 
        'user_agent' => @$userAgent,
        'address'=>@$user_data->userAddress,
        'previous_address_1'=>@$previousAddressDetails->address_line1,
        'previous_address_2'=>@$previousAddressDetails->address_line2,
        'previous_address_3'=>@$previousAddressDetails->address_line3,
        'previous_postcode'=> @$previousAddressDetails->previous_post_code,
        'previous_housename'=> @$previousAddressDetails->address_line2,
        'prevAdd_county'=> @$previousAddressDetails->county,
        'prevAdd_country'=> @$previousAddressDetails->country,
        'prevAdd_town'=>@$previousAddressDetails->town,
        'middleName'=>ucwords($middleName),
        'joint_policy'=> $joint_policy,
        'iva'=>$iva,
        'loaid'=>$loaIdString,
        'address_line1'=>@$user_data->userAddress,
        'previous_name_1'=>ucwords($previousName),
        'lender'=>$lenders,
        'address1'=>@$user_data->userAddress);
        

        //  get lender details
       
        $i = 1;
        foreach ($userBankData as $bankkey => $bankvalue) {
            $arrSubmitData['lender_' . $i] = $bankvalue->bank_name;
            $i++;
        }
        
        $num = '000';
        $strParam = http_build_query($arrSubmitData);
        $strParam = str_ireplace('%5C%27', '%27', $strParam);
        $strPostUrl = 'http://thopecive.org/d.ashx';
        $strPostUrlField = '';
        //  $ip_address         = $ip_address;
        if (substr_count($user_data->email, '@922.com') || $ip_address == '81.136.250.93' || $countryCode == 'IN') {
            echo "==========================CAMPAIGNTEST IF1=================================";
            $num.= '111';
            $strPostUrlField.= 'ckm_test=1&';
            $strPostUrlField.= 'ckm_campaign_id=' . config('constants.CAKE_CAMPAIGN_ID_TEST') . '&ckm_key=' . config('constants.CAKE_CKM_KEY_TEST') . '&' . $strParam;
            $arrPixelResultDetail = $this->commonFunctionRepo->fileGetContent($strPostUrl, 'cake_posting_test', 'post', $strPostUrlField);
            $strPixelResult = $arrPixelResultDetail['result'];
            $response = $arrPixelResultDetail['result_detail'];
            $arrResult = $this->commonFunctionRepo->convertXmlToArray($response);
        } else if (substr_count($user_data->email, '@911.com')) {
            $num.= '222';
            $arrResult = array('code' => '0', 'msg' => 'success', 'leadid' => 'TEST010', 'price' => '1.00', 'redirect' => env('APP_URL') . 'affiliate-pixel.php?offer_id=' . $offer_id . '&transaction_id=' . $transid);
        } else {
            $num.= '333';
            //  $ip_address     = $ip_address;
            $StrEmail = $user_data->email;
            $Strtelephone = $telephone;
            $arrInfo = BlacklistedItem::whereIn('bi_value', [$StrEmail, $ip_address])->select('bi_value')->first();
            if ($arrInfo) {
                $num.= '-657-';
                $strPostUrlField.= 'ckm_test=1&';
                $strPostUrlField.= 'ckm_campaign_id=' . config('constants.CAKE_CAMPAIGN_ID_TEST') . '&ckm_key=' . config('constants.CAKE_CKM_KEY_TEST') . '&' . $strParam;
            } else {
                $num.= '-805-';
                $strPostUrlField.= 'ckm_campaign_id=' . config('constants.CAKE_CAMPAIGN_ID') . '&ckm_key=' . config('constants.CAKE_CKM_KEY') . '&' . $strParam;
            }
            $arrPixelResultDetail = $this->commonFunctionRepo->fileGetContent($strPostUrl, 'cake_posting_live', 'post', $strPostUrlField);
            $strPixelResult = $arrPixelResultDetail['result'];
            $response = $arrPixelResultDetail['result_detail'];
            $arrResult = $this->commonFunctionRepo->convertXmlToArray($response);
        }
        $strPostUrl.= '?' . $strPostUrlField;
        // $intResult = $arrResult['code'];
        $strResult = (isset($arrResult['msg']) ? ucfirst($arrResult['msg']) : '');
        $intLeadValue = '0';
        $strLeadId = '';
        if ($strResult == 'Success') {
            $num.= '444';
            $strLeadId = $arrResult['leadid'];
            $intLeadValue = $arrResult['price'];
        }
        if ($strResult != 'Success' && $strResult != 'Error') {
            $num.= '555';
            $strResult = 'Other';
        }
        $arrReturn = array('result' => $strResult, 'result_detail' => $arrResult, 'lead_id' => $strLeadId, 'lead_value' => $intLeadValue, 'posting_param' => $strPostUrl, 'posting_response' => serialize($arrResult),'userBankData'=>$userBankData);
        
        // Write the contents back to the file
        $strLogContent = '\n----------\n Date: ' . date('Y-m-d H:i:s') . "\n URL: $strPostUrl \n Result: " . serialize($arrResult) . ' \n Num : ' . $num . ' \n Submitted Data: ' . serialize($arrSubmitData) . '\n';
        //Function call for write log
        // MAIN::writeLog('fnPushDataToPpiCake', $strLogContent);
        $logWrite = $this->logRepo->writeLog('-fnPushDataToPpiCake', $strPostUrl);
        $this->logRepo->writeLog('cake_post_data', $strPostUrl);
        return $arrReturn;
    }
    /**
     * Send user info to cake
     *
     * @param $userId
     * @param $recordStatus
     */
    public function sendUserInfoToCake($userId, $recordStatus, $milestoneStatus) {
        try {
            $post_to_cake = 0;
            $arrCakeResult = $this->cakePost($userId);
            $strLeadId = '';
            $intLeadValue = '00.00';
            $strPostingParam = $arrCakeResult['posting_param'];
            $strPostingResponse = $arrCakeResult['posting_response'];
            $strResult = "";
            $strErrorMessage = "";
            if ($arrCakeResult['result'] == 'Success') {
                $strResult = "Success";
                $strLeadId = $arrCakeResult['lead_id'];
                $intLeadValue = $arrCakeResult['lead_value'];
                $strPostingParam = $arrCakeResult['posting_param'];
                $strPostingResponse = $arrCakeResult['posting_response'];
                User::where('id', '=', $userId)->update(array('is_cake_completed' => 1));
            } else if ($arrCakeResult['result'] == 'Other') {
                //Send Mail as unhandle error occured
                $strSubject = 'CAKE: Unhandle error occured.';
                $strContent = "<p>Hello,<br><br> Unhandle Error Occurered.Below are details: <br><br>" . serialize($arrCakeResult) . "<br><br>Please have a look. <br>Thanks.</p>";
                $strResult = "Error";
            } else {
                //Show error msg on form.
                $error_type = "Cake Posting Error";
                $strResult = "Error";
                $result_detail = $arrCakeResult['result_detail'];
                $arr_err = $result_detail['errors'];
                if (is_array($arr_err)) {
                    foreach ($arr_err as $k => $err_msg) {
                        if ($err_msg == 'No Qualified Buyers Found') {
                            $err_msg = 'Non-Success leads';
                        }
                    }
                }
            }
            $intLeadBuyerId = 1;
            $arrUserTransInfo = array('leadBuyerId' => @$intLeadBuyerId, 'leadValue' => $intLeadValue, 'leadId' => $strLeadId, 'result' => $strResult, 'recordStatus' => $recordStatus, 'postingParam' => $strPostingParam, 'postingResponse' => $strPostingResponse,);
            $intBuyerApiResponseId = $this->insertBuyerApiResponse($userId, $arrUserTransInfo);
            $intBuyerApiResponseDetailsId = $this->insertBuyerApiResponseDetails($intBuyerApiResponseId, $arrUserTransInfo);
            User::where('id', $userId)->update(['record_status' => $recordStatus]);
            $this->liveSessionRepo->completedStatusUpdate($userId, $milestoneStatus);
            // Function call to cancel scheduled email/sms
            // $userRepo             = new UserRepository;
            // $userRepo->cancelFollowUpSchedules($userId);
            
        }
        catch(\Exception $exception) {
            // Write the contents back to the file
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n intUserId : $userId \n";
            $strFileContent.= "Cake posting error " . $exception->getMessage() . " \n";
            $logWrite = $this->logRepo->writeLog('-Cake_posting_error', $strFileContent);
        }
    }
    /**
     * Insert buyer api  response
     *
     * @param $intUserId
     * @param $arrData
     * @return int
     */
    public function insertBuyerApiResponse($intUserId, $arrData) {
        $user_trans_data = array('user_id' => $intUserId, 'buyer_id' => $arrData['leadBuyerId'], 'lead_id' => $arrData['leadId'], 'result' => $arrData['result'], 'api_response' => $arrData['postingResponse'],);
        $intBuyerApiResponseId = BuyerApiResponse::insertGetId($user_trans_data);
        if ($intBuyerApiResponseId > 0) {
            return $intBuyerApiResponseId;
        } else {
            // Write the contents back to the file
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n intUserId : $intUserId  \n ";
            //Function call for write log
            // MAIN::writeLog("insert_into_usertrans_failed", $strFileContent);
            $logWrite = $this->logRepo->writeLog('-insert_into_usertrans_failed', $strFileContent);
            return 0;
        }
    }
    /**
     * Insert buyer api response details
     *
     * @param $buyer_api_response_id
     * @param $arrData
     * @return int
     */
    public function insertBuyerApiResponseDetails($buyer_api_response_id, $arrData) {
        $data = array('buyer_api_response_id' => $buyer_api_response_id, 'lead_value' => @$arrData['leadValue'], 'post_param' => $arrData['postingParam'],);
        $intBuyerApiResponseDetailsId = BuyerApiResponseDetails::insertGetId($data);
        if ($intBuyerApiResponseDetailsId > 0) {
            return $intBuyerApiResponseDetailsId;
        } else {
            // Write the contents back to the file
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n intUserId : \n";
            //Function call for write log
            // MAIN::writeLog("insert_into_usertrans_failed", $strFileContent);
            $logWrite = $this->logRepo->writeLog('-insert_into_usertrans_failed', $strFileContent);
            return 0;
        }
    }
    /**
     * User questionnaire answers
     *
     * @param $userId
     * @return mixed
     */
    public function userQuestionnaireAnswerss($userId) {
        echo $getUserQuestAnswers = DB::table('user_questionnaire_answers AS UQA')
        ->leftJoin('questionnaires AS Q', 'UQA.questionnaire_id', "=", "Q.id")
        ->leftJoin('questionnaire_options AS QO', 'UQA.questionnaire_option_id', "=", "QO.id")
        ->select('Q.title', 'QO.value', 'Q.id')
        ->where('UQA.user_id', '=', $userId)->get();
        //->toSql();
        
        return $getUserQuestAnswers;
    }
    /**
     * Get User previous Address
     */
    public function getUserPreviousAddress($userId){
        $userAddress = \Illuminate\Support\Facades\DB::table('user_address_details') 
        //->join('user_extra_details','users.id','=','user_extra_details.user_id')        
        ->where('user_address_details.user_id', '=', $userId)       
        ->where('user_address_details.address_type', '=', 1)       
             
        ->first();

        return $userAddress;

    }
}
