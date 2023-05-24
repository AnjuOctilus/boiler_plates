<?php

namespace App\Repositories;

use App\Models\Affliate;
use App\Models\SiteConfig;
use App\Models\BuyerDetail;
use App\Models\AdvInfo;
use App\Models\DomainDetail;
use App\Models\TrackerMaster;
use Carbon\Carbon;
use DB;
use App\Repositories\LogRepository;
use Illuminate\Support\Arr;
use App\Repositories\Interfaces\CommonFunctionsInterface;
use App\Models\SplitInfo;

/**
 * Class CommonFunctionsRepository
 *
 * @package App\Repositories
 */
class CommonFunctionsRepository implements CommonFunctionsInterface
{
    /**
     * Stringcrypt
     *
     * @param $string
     * @param $action
     * @return false|string
     */
    public function stringcrypt($string, $action)
    {
        $secret_key = 'C]^82-<L';
        $secret_iv = '4Z[F!^EB';
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else if ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    /**
     * Get campaign affid
     *
     * @param string $strCampaign
     * @param $intSiteFlagId
     * @param string $intHoOfferId
     * @return int|mixed
     */
    public function getCampaignAffID($strCampaign = '', $intSiteFlagId, $intHoOfferId = '')
    {
        $obj = Affliate::select('id', 'site_flag_id')
            ->where('ho_offer_id', '=', $intHoOfferId)
            ->where('affiliate_name', '=', $strCampaign);
        if (!preg_match('/1/i', $intSiteFlagId)) {
            $obj->where(function ($q) use ($intSiteFlagId) {
                $q->where('site_flag_id', 'like', '%' . $intSiteFlagId . '%')
                    ->orWhere('site_flag_id', 'like', '%1%');
            });
        } else {
            $obj->where('site_flag_id', 'like', '%' . $intSiteFlagId . '%');
        }
        $objAff = $obj->orderBy('affiliate_id', 'DESC')->get();
        $count = $objAff->count();
        $intAffiliateId = 0;
        $arrAffiliateIds = array();
        if ($objAff && $count > 0) {
            foreach ($objAff as $key => $object) {
                $intCurAffiliateId = $object->affiliate_id;
                $strCurSiteFlags = $object->site_flag_id;
                $arrAffiliateIds[] = $intCurAffiliateId;
                if ($strCurSiteFlags == $intSiteFlagId) {
                    return $intAffiliateId = $intCurAffiliateId;
                }
            }
            $intAffiliateId = $arrAffiliateIds[0];
        }
        return $intAffiliateId;
    }

    /**
     * Check affiliate pixel
     *
     * @param $campaignId
     * @param $aff_id
     * @param $offer_id
     * @return mixed|void
     */
    public function checkAffiliatePixel($campaignId, $aff_id, $offer_id)
    {
        if (empty($campaignId) || empty($aff_id) || empty($offer_id)) return;
        $obj = Affliate::select(DB::raw('count(ho_offer_id) as cnt'), 'id')
            ->where('ho_offer_id', '=', $offer_id)
            ->where('affiliate_name', '=', $campaignId)
            ->groupBy('ho_offer_id', 'id')
            ->first();
        if ($obj) {
            $count = $obj->count();
        } else {
            $count = 0;
        }
        $intDuplicate = 0;
        if ($obj && $count > 0) {
            $intDuplicate = $obj->cnt;
            $intAffiliateId = $obj->affiliate_id;
        }
        if ($intDuplicate == 0) {
            $defAffTracking = Static::fnGetConfigValue("DEFAULT_AFFILIATE_TRACKING_PERC");
            if (empty($defAffTracking)) {
                $defAffTracking = 80;
            }
            $trackingPercentage = $defAffTracking;
            $mon_percentage = $defAffTracking;
            $tue_percentage = $defAffTracking;
            $wed_percentage = $defAffTracking;
            $thu_percentage = $defAffTracking;
            $fri_percentage = $defAffTracking;
            $sat_percentage = $defAffTracking;
            $sun_percentage = $defAffTracking;
            $mon_weightage = '0';
            $tue_weightage = '0';
            $wed_weightage = '0';
            $thu_weightage = '0';
            $fri_weightage = '0';
            $sat_weightage = '0';
            $sun_weightage = '0';
            $tracking_days = '1,2,3,4,5,6,7';
            $time = '0-86400';
            $max_pixel_day = '0';
            $status = '1';
            $aff = new Affliate();
            $aff->affiliate_name = $campaignId;
            $aff->tracking_percentage = $trackingPercentage;
            $aff->tracking_days = $tracking_days;
            $aff->time_of_day = $time;
            $aff->max_pixel_per_day = $max_pixel_day;
            $aff->tracking_counter = 0;
            $aff->tracking_batch = 0;
            $aff->active = $status;
            $aff->site_flag_id = 1;
            $aff->ho_offer_id = $offer_id;
            $aff->mon_tracking = $mon_percentage;
            $aff->tue_tracking = $tue_percentage;
            $aff->wed_tracking = $wed_percentage;
            $aff->thu_tracking = $thu_percentage;
            $aff->fri_tracking = $fri_percentage;
            $aff->sat_tracking = $sat_percentage;
            $aff->sun_tracking = $sun_percentage;
            $aff->mon_weightage = $mon_weightage;
            $aff->tue_weightage = $tue_weightage;
            $aff->wed_weightage = $wed_weightage;
            $aff->thu_weightage = $thu_weightage;
            $aff->fri_weightage = $fri_weightage;
            $aff->sat_weightage = $sat_weightage;
            $aff->sun_weightage = $sun_weightage;
            $aff->save();
            $insertedId = $aff->id;

            return $insertedId;
        }
    }

    /**
     * Fn get config value
     *
     * @param $strConfigParam
     * @return string
     */
    public static function fnGetConfigValue($strConfigParam)
    {
        $strConfigVal = '';
        $arrResult = SiteConfig::select('config_value')
            ->where('config_title', '=', $strConfigParam)->first();
        $count = $arrResult->count();
        if ($count > 0) {
            $strConfigVal = $arrResult->config_value;
        }
        return $strConfigVal;
    }

    /**
     * Create URL
     *
     * @param $fileName
     * @param $arrValues
     * @return string
     */
    public function creatURL($fileName, $arrValues)
    {
        $resultURL = $fileName;
        if (is_array($arrValues)) {
            foreach ($arrValues as $key => $value) {
                if ($resultURL != $fileName) {
                    $resultURL .= "&";
                } else {
                    $resultURL .= "?";
                }
                $resultURL .= $key . "=" . urlencode($value);
            }
        } else {
            if ($resultURL != $fileName) {
                $resultURL .= "&";
            } else {
                $resultURL .= "?";
            }
            $resultURL .= urlencode($value);
        }
        return $resultURL;
    }

    /**
     * Base64 url encode
     *
     * @param $data
     * @return string
     */
    public function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function fileGetContent($strUrl, $logType = '', $method = 'get', $arrPostFields = array())
    {
        $strResult = 'Error';
        $strMessage = '';
        if ($strUrl != '') {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $strUrl);
                curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                if (strtolower($method) == "post") {
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $arrPostFields);
                }
                $strMessage = curl_exec($ch);
                $curlInfo = curl_getinfo($ch);
                curl_close($ch);
                $strResult = 'Success';
            } catch (Exception $e) {
                $strMessage = "";
            }
        }
        $arrResult = array(
            'url' => $strUrl,
            'result' => $strResult,
            'result_detail' => $strMessage
        );
        if (!empty($logType)) {
            $logRepo = new LogRepository;
            $logWrite = $logRepo->writeLog($logType, $arrResult);
        }
        return $arrResult;
    }

    /**
     * File get content adtopia
     *
     * @param $strUrl
     * @param string $logType
     * @param string $method
     * @param array $arrPostFields
     * @return array
     */
    public function fileGetContentAdtopia($strUrl, $logType = '', $method = 'get', $arrPostFields = array())
    {
        $strResult = 'Error';
        $strMessage = '';
        $header = array(
            "Authorization: Bearer " . env('ADTOPIA_TOKEN'),
            "Accept: application/json"
        );
        if ($strUrl != '') {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $strUrl);
                curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                if (strtolower($method) == "post") {
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $arrPostFields);
                }
                $strMessage = curl_exec($ch);
                $curlInfo = curl_getinfo($ch);
                curl_close($ch);
                $strResult = 'Success';
            } catch (Exception $e) {
                $strMessage = "";
            }
        }
        $arrResult = array(
            'url' => $strUrl,
            'result' => $strResult,
            'result_detail' => $strMessage
        );
        if (!empty($logType)) {
            $logRepo = new LogRepository;
            $logWrite = $logRepo->writeLog($logType, $arrResult);
        }
        return $arrResult;
    }

    /**
     * Get data key
     *
     * @param null $visitor_id
     * @return \Laravel\Lumen\Application|mixed
     */
    public function getDataKey($visitor_id = null)
    {
        $visitor_id = trim($visitor_id);
        $strReturnKey = config('constants.DATA_KEY_TEST');
        if (!empty($visitor_id > 0)) {
            $buyerDetail = DB::table('visitors')
                ->join('split_info', 'visitors.split_id', '=', 'split_info.id')
                ->join('buyer_details', 'buyer_details.id', '=', 'split_info.buyer_id')
                ->where('visitors.id', '=', $visitor_id)
                ->select('buyer_details.data_key')
                ->first();
            if (!empty($buyerDetail)) {
                if (isset($buyerDetail->data_key) && !empty($buyerDetail->data_key)) {
                    $strReturnKey = $buyerDetail->data_key;
                }
            }
        }
        if (empty($strReturnKey) && (env('APP_ENV') == 'live')) {
            $strReturnKey = config('constants.DATA_KEY_LIVE');
        } elseif (env('APP_ENV') == 'local') {
            $strReturnKey = config('constants.DATA_KEY_TEST');
        } elseif (env('APP_ENV') == 'dev') {
            $strReturnKey = config('constants.DATA_KEY_TEST');
        }
        return $strReturnKey;
    }

    /**
     * Is test live email
     *
     * @param $email
     * @return string
     */
    public function isTestLiveEmail($email)
    {
        $check1 = substr_count(strtolower($email), "@922.com");
        $check2 = substr_count(strtolower($email), "@911.com");
        return (($check1 == 0 && $check2 == 0) ? 'LIVE' : 'TEST');
    }

    /**
     * Get posting lead buyer
     *
     * @return string
     */
    public function getPostingLeadBuyer()
    {
        return $result = 'CAKE';
    }

    /**
     * Get lead buyer id
     *
     * @param string $strLeadBuyerName
     * @return int
     */
    public function getLeadBuyerID($strLeadBuyerName = 'CAKE')
    {
        $intLeadBuyerId = 0;
        $ArrBuyer = BuyerDetail::select('id')
            ->where('buyer_name', '=', $strLeadBuyerName)
            ->where('status', '=', 1)
            ->first();
        if (!empty($ArrBuyer)) {
            $intLeadBuyerId = $ArrBuyer->id;
        }
        return $intLeadBuyerId;
    }

    /**
     * Get DdMmYyyy
     *
     * @param $date
     * @return array
     */
    public function getDdMmYyyy($date)
    {
        if ($date) {
            $date = explode('/', $date);

            return $array = (
            [
                'DD' => $date[0],
                'MM' => $date[1],
                'YYYY' => $date[2]
            ]
            );
        }
    }

    /**
     * Get client ip
     *
     * @return array|false|string
     */
    public function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    /**
     * Change date format
     *
     * @param $strFormat
     * @param string $strDate
     * @return string
     */
    public function changeDateFormat($strFormat, $strDate = '00-00-0000')
    {
        if ($strDate == '') {
            $strDate = '00-00-0000';
        }
        return date($strFormat, strtotime($strDate));
    }

    /**
     * Convert xml to array
     *
     * @param $xml
     * @param string $main_heading
     * @return mixed
     */
    public function convertXmlToArray($xml, $main_heading = '')
    {
        $deXml = simplexml_load_string($xml);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson, TRUE);
        if (!empty($main_heading)) {
            $returned = $xml_array[$main_heading];
            return $returned;
        } else {
            return $xml_array;
        }
    }

    /**
     * Dynamic advertorials add
     *
     * @param $strFileName
     * @param $splitPath
     * @param null $domain_name
     */
    public function dynamicAdvertorialsAdd($strFileName, $splitPath, $domain_name = NULL)
    {
        $strSplitName = strtolower($strFileName);
        $splitInfo = AdvInfo::select('id')
            ->where('adv_name', 'LIKE', $strSplitName)
            ->where('adv_path', 'LIKE', $splitPath)
            ->count();

        if ($splitInfo == 0) {
            if ($domain_name == NULL) {
                $domain_name = env('APP_URL');
            }
            $domain_result = DomainDetail::select('id', 'type')
                ->where('domain_name', '=', $domain_name)
                ->first();
            if (!empty($domain_result)) {
                $domain_id = $domain_result->id;
                $domain_type = $domain_result->type;
                if ($domain_result->type == 'LP') {
                    $domain_result->update(array('type' => 'Both'));
                }
            } else {
                $domainDetail = new DomainDetail;
                $domainDetail->domain_name = $domain_name;
                $domainDetail->type = 'Adv';
                $domainDetail->last_active_date = Carbon::now();
                $domainDetail->save();
                $domain_id = $domainDetail->id;
            }
            $objSplitInfo = new AdvInfo;
            $objSplitInfo->domain_id = $domain_id;
            $objSplitInfo->adv_name = $strFileName;
            $objSplitInfo->adv_path = $splitPath;
            $objSplitInfo->last_active_date = Carbon::now();
            $objSplitInfo->save();
            
            //return ID of newly added adv page
            return $objSplitInfo->id;
        } else {
            $advInfo = AdvInfo::select('id')
                ->where('adv_name', 'LIKE', $strSplitName)
                ->where('adv_path', 'LIKE', $splitPath)
                ->orderBy('id', 'DESC')
                ->first();
            if (!empty($advInfo)) {
                $intAdvertorialId = $advInfo->id;
            }
            return isset($intAdvertorialId) ? $intAdvertorialId : '';
        }
    }

    /**
     * Get advertorial id from name
     *
     * @param $strAdvName
     * @param $intSiteFlagId
     * @return string
     */
    public function getAdvertorialIdFromName($strAdvName, $intSiteFlagId)
    {
        $intSplitId = 0;
        $advInfo = AdvInfo::select('id')
            ->where("adv_name", "=", $strAdvName)
            ->first();
        if (!empty($advInfo)) {
            $intAdvertorialId = $advInfo->id;
        }
        return isset($intAdvertorialId) ? $intAdvertorialId : '';
    }

    /**
     * Get tracker type
     *
     * @param $trackerId
     * @return string
     */
    public function getTrackerType($trackerId)
    {
        $tracker_name = "";
        $trackInfo = TrackerMaster::select('tracker_name')
            ->where("id", "=", $trackerId)
            ->first();
        if (!empty($trackInfo)) {
            $tracker_name = $trackInfo->tracker_name;
        }
        return $tracker_name;
    }

    /**
     * Get domain id
     *
     * @param null $domain_name
     * @return int
     */
    public function getDomainId($domain_name = NULL)
    {
        $domain_id = 0;
        if ($domain_name == NULL) {
            $domain_name = env('APP_URL');
        }

        $domain_result = DomainDetail::select('id', 'type')
            ->where('domain_name', '=', $domain_name)
            ->first();
        if (!empty($domain_result)) {
            $domain_id = $domain_result->id;
        }
        return $domain_id;
    }

    /**
     * Send SMS
     *
     * @param $recipient
     * @param $content
     * @return array
     */
    public function sendSMS($recipient, $content)
    {
        $username = env('SMS_USERNAME');
        $pwd = env('SMS_PASSWORD');
        $account = env('SMS_ACCOUNT');
        $postdata = 'username=' . $username . '&account=' . $account . '&password=' . $pwd . '&recipient=' . $recipient . '&body=' . $content . '&plaintext=1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('SMS_STRATEGY_URL'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Length: ' . strlen($postdata))
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            $sms_status = "";
            $sms_status_txt = "";
            $arr_sms = explode("MessageIDs=", $response);
            $message_id = isset($arr_sms[1]) ? $arr_sms[1] : '';
            if ($message_id != '') {
                $sms_status = CommonFunctionsRepository::getStatusofSMS($message_id);
                $sms_status = (array)$sms_status;
                $sms_status_txt = @$sms_status[0];
            } else {
                $sms_status_txt = 'Invalid Message ID';
            }
            $resp = array("status" => $sms_status_txt, "response" => $httpcode);
            $log_stat = serialize($sms_status);
        } else {
            $sms_status = "";
            $sms_status_txt = "Error";
            $resp = array("status" => $sms_status_txt, "response" => $response);
            $log_stat = $sms_status;
        }
        // Write into log files
        $curdate = Carbon::now();

        return $resp;
    }

    /**
     * Get status of SMS
     *
     * @param $msgid
     * @return false|\SimpleXMLElement|string
     */
    public function getStatusofSMS($msgid)
    {
        $username = env('SMS_USERNAME');
        $pwd = env('SMS_PASSWORD');
        $account = env('SMS_ACCOUNT');
        $acntdetails = $username . ":" . $pwd;
        // SMS BATCH PROCESSING
        $header = array(
            'Accept: application/xml',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($acntdetails)
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.esendex.com/v1.0/messageheaders/" . $msgid);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcodesms = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcodesms == 200) {
            $messagebatch = CommonFunctionsRepository::fnConvertXmlToArray($response);
            if (isset($messagebatch->status)) {
                $msg_stat = $messagebatch->status;
            } else {
                $msg_stat = 'Status Invalid';
            }
        } else {
            $msg_stat = "Message sent";
        }
        $curdate = Carbon::now();

        return $msg_stat;
    }

    /**
     * Fn convert XML to array
     *
     * @param $xml
     * @param string $main_heading
     * @return false|\SimpleXMLElement|string|null
     */
    public function fnConvertXmlToArray($xml, $main_heading = '')
    {
        $deXml = simplexml_load_string($xml);
        if (!empty($main_heading)) {
            $returned = $deXml[$main_heading];
            return $returned;
        } else {
            return $deXml;
        }
    }

    /**
     * Send email
     *
     * @param $recipient
     * @param $content
     * @param $subject
     * @return array
     */
    public function sendEmail($recipient, $content, $subject)
    {

        $from_name = str_replace('_', " ", env('EMAIL_FROM_NAME'));
        $from_email = env('EMAIL_FROM');
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . env('EMAIL_API_KEY')
        );
        $postdata = '{"personalizations": [{"to": [' . $recipient . ']}],"from": {"name" : "' . $from_name . '", "email": "' . $from_email . '"},"subject":"' . $subject . '","content": [{"type": "text/html","value": "' . $content . '"}]}';
    //    dd($postdata);
       
       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('EMAIL_STRATEGY_URL'));
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200 || $httpcode == 202) {
            $resp = array("status" => "Success", "response" => $httpcode);
        } else {
            $resp = array("status" => "Error", "response" => $response);
        }
        $curdate = Carbon::now();

        return $resp;
    }

    /**
     * Fetch user
     *
     * @param $user_id
     * @return array
     */
    public function fetchUserBanks($user_id)
    {
        if ($user_id != '') {
            $arrData = [];
            $user_data = DB::table('users as user')
                ->leftJoin('user_banks as ub', 'ub.user_id', '=', 'user.id')
                ->leftJoin('banks  as b', 'b.id', '=', 'ub.bank_id')
                ->select('user.id as user_id', 'ub.bank_id as bank_id', 'b.bank_code as bank_code', 'b.bank_name as bank_name')
                ->where('user.id', $user_id)
                ->get();
            if ($user_data) {
                foreach ($user_data as $key => $value) {
                    $arrData[] = $value;
                }
                return $arrData;
            }
        }
    }

    /**
     * Fetch user loa pdf
     *
     * @param $user_id
     * @return array|mixed
     */
    public function fetchUserLoaPdf($user_id)
    {
        if ($user_id != '') {
            $loa_pdf_data = [];
            $user_data = DB::table('lead_docs as ld')
                ->select('ld.bank_loa_pdf_files')
                ->where('ld.user_id', $user_id)
                ->get();
            if (isset($user_data->bank_loa_pdf_files) && ($user_data->bank_loa_pdf_files != null)) {
                foreach ($user_data as $key => $value) {
                    $loa_pdf_data = $value->bank_loa_pdf_files;
                    $loa_pdf_data = json_decode($loa_pdf_data, true);
                }
            }
            return $loa_pdf_data;
        }
    }

    /**
     * Fetch user claim id
     *
     * @param $user_id
     * @return array
     */
    public function fetchUserClaimid($user_id)
    {
        if ($user_id != '') {
            $arrData = [];
            $user_data = DB::table('api_histories_new as ap')
                ->select('ap.buyer_api_id', 'ap.request', 'status')
                ->where('ap.user_id', $user_id)
                ->where('request_type', 'CreateClaim')
                ->where('status', 'success')
                ->get();
            if ($user_data) {
                foreach ($user_data as $key => $value) {
                    $buyer_api_id = $value->buyer_api_id;
                    $buyer_api_request = $value->request;
                    $buyer_api_request = json_decode($buyer_api_request);
                    $arrData[$buyer_api_request->LenderId] = $buyer_api_id;
                }
            }
            return $arrData;
        }
    }

    /**
     * Get split id from name
     *
     * @param $strSplitName
     * @param $intSiteFlagId
     * @return int
     */
    public function getSplitIdFromName($strSplitName, $intSiteFlagId)
    {
        $intSplitId = 0;
        $splitInfo = SplitInfo::select('id')
            ->where('split_name', '=', $strSplitName)
            ->first();
        if (!empty($splitInfo)) {
            $intSplitId = $splitInfo->id;
        }
        return $intSplitId;
    }

    /**
     * Dynamic split add new
     *
     * @param $strFileName
     * @param $splitPath
     * @param $domainName
     * @return mixed
     */
    public function dynamicSplitAddNew($strFileName, $splitPath, $domainName)
    {

        $strSplitName = strtolower($strFileName);
        $splitInfo = SplitInfo::where('split_name', '=', $strSplitName)->first();

        if (!$splitInfo) {
            $domain_name = $domainName;
            $domain_result = DomainDetail::select('id', 'type')
                ->where('domain_name', '=', $domain_name)
                ->first();
            if (!empty($domain_result)) {
                $domain_id = $domain_result->id;
                $domain_type = $domain_result->type;
                if ($domain_result->type == 'Adv') {
                    $domain_result->update(array('type' => 'Both'));
                }
            } else {
                $domainDetail = new DomainDetail;
                $domainDetail->domain_name = $domain_name;
                $domainDetail->type = 'LP';
                $domainDetail->last_active_date = Carbon::now();
                $domainDetail->save();
                $domain_id = $domainDetail->id;
            }
            $objSplitInfo = new SplitInfo;
            $objSplitInfo->domain_id = $domain_id;
            $objSplitInfo->split_name = $strFileName;
            $objSplitInfo->split_path = $splitPath;
            $objSplitInfo->last_active_date = Carbon::now();
            $objSplitInfo->save();

            return $objSplitInfo->id;

        } else {
            return $splitInfo->id;
        }
    }

    /**
     * Post code format
     *
     * @param $postcode
     * @return string
     */
    public function postcodeFormat($postcode)
    {
        //remove non alphanumeric characters
        $cleanPostcode = preg_replace("/[^A-Za-z0-9]/", '', $postcode);

        //make uppercase
        $cleanPostcode = strtoupper($cleanPostcode);

        //insert space
        $postcode = substr($cleanPostcode, 0, -3) . " " . substr($cleanPostcode, -3);

        return strtoupper($postcode);
    }
}
