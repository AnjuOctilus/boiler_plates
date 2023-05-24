<?php

namespace App\Helpers;

use App\Affliate;
use App\SiteConfig;
use App\BuyerDetail;
use App\AdvInfo;
use App\DomainDetail;
use App\TrackerMaster;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use App\Repositories\LogRepository;

class CommonFunctions
{
	public static function stringcrypt($string,$action)
	{
		$secret_key     = 'C]^82-<L';
		$secret_iv      = '4Z[F!^EB';

		$output         = false;
		$encrypt_method = "AES-256-CBC";
		$key            = hash( 'sha256', $secret_key );
		$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

		if ( $action == 'e' ) {
			$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
		} else if( $action == 'd' ) {
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}
		return $output;
	}

	public static function getCampaignAffID($strCampaign = '', $intSiteFlagId, $intHoOfferId = '')
	{
		$obj = Affliate::select('id', 'site_flag_id')
						  ->where('ho_offer_id', '=', $intHoOfferId)
    					  ->where('affiliate_name', '=', $strCampaign);
    	if (!preg_match('/1/i', $intSiteFlagId)) {
    		$obj->where(function($q) use ($intSiteFlagId){
		          $q->where('site_flag_id', 'like', '%'.$intSiteFlagId.'%')
		          		 ->orWhere('site_flag_id', 'like', '%1%');
		      });
    	} else {
    		$obj->where('site_flag_id', 'like', '%'.$intSiteFlagId.'%');
    	}
    	$objAff = $obj->orderBy('affiliate_id', 'DESC')->get();
    	$count = $objAff->count();
    	$intAffiliateId = 0;
	    $arrAffiliateIds = array();
	    if ($objAff && $count > 0) {
	        foreach ($objAff as $key=>$object) {
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

	public static function checkAffiliatePixel($campaignId, $aff_id, $offer_id)
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
	        $aff->affiliate_name 				= $campaignId;
	        $aff->tracking_percentage 			= $trackingPercentage;
	        $aff->tracking_days 				= $tracking_days;
	        $aff->time_of_day 					= $time;
	        $aff->max_pixel_per_day 			= $max_pixel_day;
	        $aff->tracking_counter 				= 0;
	        $aff->tracking_batch 				= 0;
	        $aff->active 						= $status;
	        $aff->site_flag_id 					= 1;
	        $aff->ho_offer_id 					= $offer_id;
	        $aff->mon_tracking 					= $mon_percentage;
	        $aff->tue_tracking 					= $tue_percentage;
	        $aff->wed_tracking 					= $wed_percentage;
	        $aff->thu_tracking 					= $thu_percentage;
	        $aff->fri_tracking 					= $fri_percentage;
	        $aff->sat_tracking 					= $sat_percentage;
	        $aff->sun_tracking 					= $sun_percentage;
	        $aff->mon_weightage 				= $mon_weightage;
	        $aff->tue_weightage 				= $tue_weightage;
	        $aff->wed_weightage 				= $wed_weightage;
	        $aff->thu_weightage 				= $thu_weightage;
	        $aff->fri_weightage 				= $fri_weightage;
	        $aff->sat_weightage 				= $sat_weightage;
	        $aff->sun_weightage 				= $sun_weightage;
	        $aff->save();
	        $insertedId = $aff->id;
	        return $insertedId;
	    }
	}

	public static function fnGetConfigValue($strConfigParam) 
	{
		$strConfigVal = '';
		$arrResult = SiteConfig::select('config_value')
						  ->where('config_title', '=', $strConfigParam)->first(); 
	    if (isset($arrResult)) {
	        $strConfigVal = $arrResult->config_value;
	    }
	   
	    return $strConfigVal;
	}
	public static function creatURL($fileName, $arrValues)
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
	public static function base64url_encode($data) 
	{
	  	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
	public static function md5base64url_encode($visitorId,$userId) 
	{
	  	return md5(base64_encode( $visitorId . config( 'constants.VU_SEPARATOR' ) . $userId));
	}
	public static function sanitize($request)
	{
 		$input = $request;
 		//sanitize user-id
        $input['user_id'] = isset($input['user_id'])?filter_var($input['user_id'], FILTER_SANITIZE_NUMBER_INT):'';
        $input['user_id'] = (int) $input['user_id'];
        //sanitize visitor-id
        $input['visitor_id'] = isset($input['visitor_id'])?filter_var($input['visitor_id'], FILTER_SANITIZE_NUMBER_INT):'';
        $input['visitor_id'] = (int) $input['visitor_id'];
        return $input;
	}
	public static function fileGetContent($strUrl, $logType = '', $method = 'get', $arrPostFields = array())
	{
 		$strResult 	= 'Error';
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
 		$arrResult = array('url'=>$strUrl,
 			'result' 	=> $strResult, 
 			'result_detail' => $strMessage);
 		if (!empty($logType)) {
 			// MAIN::writeLog($logType, $strLogContent);
 			$logRepo        = new LogRepository;
			$logWrite       = $logRepo->writeLog($logType,$arrResult);
 		}
 		return $arrResult;
 	}
 	public static function getDataKey($visitor_id=0)
 	{
 		$visitor_id 		=	trim($visitor_id);
 		$strReturnKey 		=	config('constants.DATA_KEY_TEST');
 		if (!empty($visitor_id>0)) {
 			$buyerDetail 	=	DB::table('visitors')
 									->join('split_info','visitors.split_id','=','split_info.id')
 									->join('buyer_details','buyer_details.id','=','split_info.buyer_id')
 									->where('visitors.id','=',$visitor_id)
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
 	public static function isTestLiveEmail($email)
 	{
		$check1 = substr_count(strtolower($email),"@922.com");
    	$check2 = substr_count(strtolower($email),"@911.com");
    	return  (($check1 == 0 && $check2 == 0 ) ? 'LIVE' : 'TEST');
 	}
 	public static function getPostingLeadBuyer() 
 	{
		return $result = 'CAKE';
	}
	public static function getLeadBuyerID($strLeadBuyerName = 'CAKE') 
	{
		$intLeadBuyerId = 0;
		$ArrBuyer 				=	BuyerDetail::select('id')
										->where('buyer_name','=',$strLeadBuyerName)
										->where('status','=',1)
										->first();
		if (!empty($ArrBuyer)) {
			$intLeadBuyerId = $ArrBuyer->id;
		}
		return $intLeadBuyerId;
	}
	public static function getDdMmYyyy($date)
	{
		if($date){
			$date=explode('/', $date);
			return $array = (['DD' => $date[0], 'MM' => $date[1],'YYYY'=>$date[2]]);
		}
	}
	public static function get_client_ip() 
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
    public static function changeDateFormat($strFormat, $strDate = '00-00-0000')
    {
        if ($strDate == '') {
			$strDate = '00-00-0000';
		}
		return date($strFormat, strtotime($strDate));
	}
	public static function convertXmlToArray($xml, $main_heading = '')
    {

		$deXml 		= simplexml_load_string($xml);
		$deJson 	= json_encode($deXml);
		$xml_array 	= json_decode($deJson, TRUE);

		if (!empty($main_heading)) {
			$returned = $xml_array[$main_heading];
			return $returned;
		} 
		else {
			return $xml_array;
		}
	}
	public static function dynamicAdvertorialsAdd($strFileName,$splitPath)
	{
		$strSplitName 	= strtolower($strFileName);
		$splitInfo 		= AdvInfo::select('id')
							->where('adv_name','LIKE',$strSplitName)
							->where('adv_path','LIKE',$splitPath)
							->count();
		if($splitInfo == 0){
			$domain_name 	=	env('APP_URL');
			$domain_result 	=	DomainDetail::select('id','type')
									->where('domain_name','=',$domain_name)
									->first();
			if(!empty($domain_result)) {
				$domain_id   =  $domain_result->id;
				$domain_type =  $domain_result->type;
				if($domain_result->type == 'LP' ){
					$domain_result->update(array('type'=>'Both'));
				}
				
			}else{
				$domainDetail 				=	new DomainDetail;
				$domainDetail->domain_name	=	$domain_name;
				$domainDetail->type			=	'Adv';
				$domainDetail->last_active_date=Carbon::now();
				$domainDetail->save();
				$domain_id 					=	$domainDetail->id;
 			}
 			$objSplitInfo 					=	new AdvInfo;
 			$objSplitInfo->domain_id 		=	$domain_id;
 			$objSplitInfo->adv_name 		=	$strFileName;
 			$objSplitInfo->adv_path 		=	$splitPath;
 			$objSplitInfo->last_active_date =	Carbon::now();
 			$objSplitInfo->save();
		}

	}
		public static function getAdvertorialIdFromName($strAdvName, $intSiteFlagId) 
	{
		$intSplitId 		=	0;
		$advInfo 			=	AdvInfo::select('id')
									->where("adv_name","=",$strAdvName)
									->first();
		if(!empty($advInfo)){
			$intAdvertorialId  	= 	$advInfo->id;
		}
		return $intAdvertorialId;
	}
		public static function getTrackerType($trackerId)
	{
		$tracker_name  	=	"";
		$trackInfo 		=	TrackerMaster::select('tracker_name')
									->where("id","=",$trackerId)
									->first();
		if(!empty($trackInfo)){
			$tracker_name  	= 	$trackInfo->tracker_name;
		}
		return $tracker_name;
	}
		public static function getDomainId()
	{
		$domain_id   	= 0;
		$domain_name 	=	env('APP_URL');
		$domain_result 	=	DomainDetail::select('id','type')
								->where('domain_name','=',$domain_name)
								->first();
		if(!empty($domain_result)) {
			$domain_id   =  $domain_result->id;
		}
		return $domain_id;

	}
	public static function sendSMS($recipient,$content)
	{
		$username   = env('SMS_USERNAME');
		$pwd        = env('SMS_PASSWORD');
		$account    = env('SMS_ACCOUNT');

		$postdata   = 'username='.$username.'&account='.$account.'&password='.$pwd.'&recipient='.$recipient.'&body='.$content.'&plaintext=1';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,env('SMS_STRATEGY_URL'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Accept: application/json',
		'Content-Length: ' . strlen($postdata))
		);      
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);
		if($httpcode == 200)
		{
			$sms_status="";
			$sms_status_txt="";
			$arr_sms = explode("MessageIDs=", $response);
			$message_id = isset($arr_sms[1]) ? $arr_sms[1] : '';
			if($message_id!='')
			{
				$sms_status = CommonFunctions::getStatusofSMS($message_id);
				$sms_status = (array) $sms_status;
				$sms_status_txt = @$sms_status[0];

			}
			else
			{
				$sms_status_txt = 'Invalid Message ID';
			}

			$resp = array("status" => $sms_status_txt , "response" => $httpcode);

			$log_stat = serialize($sms_status);
		}
		else
		{
			$sms_status="";
			$sms_status_txt = "Error";
			$resp = array("status" => $sms_status_txt, "response" => $response);

			$log_stat = $sms_status;

		}
		// Write into log files
		$curdate      =  Carbon::now();

		return $resp;
	}
	public static function getStatusofSMS($msgid)
	{
		$username   = env('SMS_USERNAME');
		$pwd        = env('SMS_PASSWORD');
		$account    = env('SMS_ACCOUNT');
		$acntdetails = $username.":".$pwd;
		// SMS BATCH PROCESSING 
		$header = array(
		    'Accept: application/xml',
		    'Content-Type: application/json',
		    'Authorization: Basic '. base64_encode($acntdetails)
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.esendex.com/v1.0/messageheaders/".$msgid);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcodesms = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close ($ch);
		if($httpcodesms == 200)
		{
			$messagebatch = CommonFunctions::fnConvertXmlToArray($response);
			if(isset($messagebatch->status))
			{
				$msg_stat = $messagebatch->status;
			}
			else
			{
				$msg_stat = 'Status Invalid';
			}
		}
		else
		{
			$msg_stat = "Message sent";
		}

		$curdate      =  Carbon::now();
		return $msg_stat;
	} 

 	public static function fnConvertXmlToArray($xml, $main_heading = '') {
	    $deXml = simplexml_load_string($xml);
	    if (!empty($main_heading)) {
	        $returned = $deXml[$main_heading];
	        return $returned;
	    } else {
	        return $deXml;
	    }
	}
	public static function sendEmail($recipient,$content,$subject)
	{
		$from_name = str_replace('_', " ",env('EMAIL_FROM_NAME'));
		$from_email = env('EMAIL_FROM');
		$header = array(
		'Content-Type: application/json',
		'Authorization: Bearer '.env('EMAIL_API_KEY'));
		$postdata = '{"personalizations": [{"to": ['.$recipient.']}],"from": {"name" : "'.$from_name.'", "email": "'.$from_email.'"},"subject":"'.$subject.'","content": [{"type": "text/html","value": "'.$content.'"}]}';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,env('EMAIL_STRATEGY_URL'));
		curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);
		if($httpcode == 200 || $httpcode == 202)
		{
			$resp = array("status" => "Success", "response" => $httpcode);
		}
		else
		{
			$resp = array("status" => "Error", "response" => $response);
		}

		$curdate      =  Carbon::now();

		return $resp;
	}
}