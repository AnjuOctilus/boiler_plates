<?php

namespace App\Repositories;

use App\Models\PostcodeValidation;
use App\Models\ValidationPhoneTracking;
use App\Models\User;
use App\Models\ValidationEmailTracking;
use App\Repositories\LogRepository;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\EmailRepository;
use SoapClient;
use Config;
use DB;
use App\Repositories\Interfaces\ValidationInterface;
/**
 * Class ValidationRepository
 *
 * @package App\Repositories
 */
class ValidationRepository implements ValidationInterface
{
    /**
     * ValidationRepository constructor.
     *
     */
    public function __construct()
    {
        $this->logRepo              = new LogRepository;
        $this->commonFunctionRepo     = new CommonFunctionsRepository;
        $this->emailRepo               = new EmailRepository;
        $this->logRepo              = new LogRepository;
    }
    /**
     * Check post code Tp api
     *
     * @param $strPostcode
     * @return string
     */
    public function checkPostalCodeTpApi($strPostcode)
    {
        $strResult         =    static::localPostcodeValidation($strPostcode);
        if ($strResult == 'Error') {  //check with 3rd party
            $strResult  =     static::isValidPostcode($strPostcode);
        }
        $strResult         =    (($strResult == 'INVALID') ? 'Error' : 'Success');
        return $strResult;
    }
    /**
     * Check valid phone number api
     *
     * @param $strTelephone
     * @param $intVisitorId
     * @return string
     */
    public function CheckValidPhoneNumberApi($strTelephone, $intVisitorId)
    {
        $strResult = static::fnLocalPhoneValidation($strTelephone);
        if ($strResult == '-1') {
            $strResult = static::fnPhoneVerification($strTelephone, $intVisitorId); //check with 3rd party API
        }
        return $strResult;
    }
    /**
     * Fn local phone validation
     *
     * @param $str_phone
     * @param string $strPhoneType
     * @return string
     */
    public function fnLocalPhoneValidation($str_phone, $strPhoneType = '')
    {
        $res_sel_postcode     =    ValidationPhoneTracking::select('validation_type', 'validation_result')
            ->where('phone_number', '=', $str_phone);

        if ($strPhoneType == 'Telephone') {
            $res_sel_postcode->where('validation_type', 'in', ['InternationalTelephoneValidation', 'TelephoneLineValidation']);
        } else if ($strPhoneType == 'Mobile') {
            $res_sel_postcode->where('validation_type', 'in', ['InternationalTelephoneValidation', 'MobileValidation']);
        }

        $recordCnt = $res_sel_postcode->get()->count();
        $records = $res_sel_postcode->get();

        if ($recordCnt > 0) {
            foreach ($records as $key => $val) {
                $strValidationResult = $val->validation_result;
                $strValidationtype = $val->validation_type;
                if ($strValidationtype == 'InternationalTelephoneValidation') {
                    if ($strValidationResult == 'Invalid' || $strValidationResult == 'Error') {
                        return 'Error';
                    }
                } else if ($strValidationtype == 'TelephoneLineValidation') {
                    return (($strValidationResult == 'Invalid' || $strValidationResult == 'Error') ? 'Error' : 'Success');
                } else if ($strValidationtype == 'MobileValidation') {
                    return (($strValidationResult == 'Invalid' || $strValidationResult == 'Error') ? 'Error' : 'Success');
                }
            }
            return 'Error';
        }
        return '-1';
    }
    /**
     * Check phone number exist
     *
     * @param $str_phone
     * @return int
     */
    public function CheckPhoneNumberExist($str_phone)
    {
        $res_phone         =    ValidationPhoneTracking::select('validation_type', 'validation_result')
            ->where('phone_number', '=', $str_phone)
            ->where('validation_result', '=', 'Valid');
        $recordCount     = $res_phone->get()->count();
        if ($recordCount > 0) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Check phone tracking
     *
     * @param $str_phone
     * @param $validation_result
     * @return int
     */
    public function checkPhonetracking($str_phone, $validation_result)
    {
        $check_phone         =    ValidationPhoneTracking::select('phone_number', 'validation_result')
            ->where('phone_number', '=', $str_phone)
            ->where('validation_result', '=', $validation_result);
        $checkPhoneCount     = $check_phone->get()->count();
        if ($checkPhoneCount > 0) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Fn phone verification
     *
     * @param $telephoneNumber
     * @param $visitor_id
     * @param string $strPhoneType
     * @return string
     * @throws \SoapFault
     */
    public function fnPhoneVerification($telephoneNumber, $visitor_id, $strPhoneType = '')
    {
        $result_verification = "Error";
        $options = array(
            "UseMobileValidation" => "True",
            "UseLineValidation" => "True",
            "AllowedPrefixes" => "+441,+442"
        );
        $defaultCountry = 44;
        $params = array(
            "username" => config('constants.DATA8_USERNAME'),
            "password" => config('constants.DATA8_PASSWORD'),
            "telephoneNumber" => $telephoneNumber,
            "defaultCountry" => $defaultCountry,
            "options" => $options
        );
        // Setting read timeout in seconds
        ini_set('default_socket_timeout', 10);
        // Setting request timeout in seconds as an option
        $client = new \SoapClient("https://webservices.data-8.co.uk/InternationalTelephoneValidation.asmx?WSDL", array('connection_timeout' => 10));
        // Try block starts
        try {
            $xml_result = $client->IsValid($params);

            $arrResult = json_decode(json_encode((array) $xml_result), 1);

            $arrValidStatus         = $arrResult['IsValidResult']['Status'];
            $arrValidResult         = $arrResult['IsValidResult']['Result'];
            $int_result             = $arrValidStatus['Success'];   //1,0 : check or not at service
            $int_credit_rem         = $arrValidStatus['CreditsRemaining']; //Remaining credit
            $str_verify_tel         = $arrValidResult['TelephoneNumber'];
            $str_verify_result         = $arrValidResult['ValidationResult']; // Invalid / Valid
            $str_verify_level         = $arrValidResult['ValidationLevel'];
            $str_verify_numType     = $arrValidResult['NumberType'];
            $str_verify_location     = (isset($arrValidResult['Location']) ? $arrValidResult['Location'] : "");
            $str_verify_provider     = (isset($arrValidResult['Provider']) ? $arrValidResult['Provider'] : "");
            $str_verify_countryCode = (isset($arrValidResult['CountryCode']) ? $arrValidResult['CountryCode'] : "");
            $str_verify_country     = (isset($arrValidResult['CountryName']) ? $arrValidResult['CountryName'] : "");
            $result_type             = "NONE";

            if ($int_credit_rem == 100 || $int_credit_rem == 50 || $int_credit_rem == 25) {
                $this->emailRepo->fnSendGeneralMailAWS("Phone Verification", "<p>Function : fnPhoneVerification() <br>Internation Telephone Validation >>  " . $int_credit_rem . " Remaining</p>");
            }
            if ($int_credit_rem <= 0) {    //Credit Finish. Bipass
                $result_detail = "SKIP";
                $result_verification = "SUCCESS";
                $str_verify_result = "Valid";
            } else {
                if ($int_result == 0) {
                    $result_detail = "ErrorMessage:" . $arrValidStatus['ErrorMessage'];
                    $result_verification = "Error";
                } else {
                    if ($str_verify_result == 'Valid') {
                        $is_validate_temp = 0;
                        if ($str_verify_numType == 'Landline' && ($strPhoneType == 'Landline' || $strPhoneType == '')) {
                            $is_validate_temp = 1;
                            $result_verification = static::fnLandlineVerification($telephoneNumber, $visitor_id);
                            if ($result_verification == "Success") {
                                $result_type = "LANDLINE";
                            }
                        } else if ($str_verify_numType == 'Mobile' && ($strPhoneType == 'Mobile' || $strPhoneType == '')) {
                            $is_validate_temp = 1;
                            $result_verification = static::fnMobileVerification($telephoneNumber, $visitor_id);
                            if ($result_verification == "Success") {
                                $result_type = "MOBILE";
                            }
                        }

                        if ($is_validate_temp == 0) {
                            if (($strPhoneType == 'Mobile' || $strPhoneType == '') && (substr($telephoneNumber, 0, 2) == '07' || substr($telephoneNumber, 0, 1) == '7')) { //MOBILE
                                $result_verification = static::fnMobileVerification($telephoneNumber, $visitor_id);
                                if ($result_verification == "Success") {
                                    $result_type = "MOBILE";
                                }
                            } else if (($strPhoneType == 'Landline' || $strPhoneType == '') && (substr($telephoneNumber, 0, 2) != '07' && substr($telephoneNumber, 0, 1) != '7')) {  //LANDLINE
                                $result_verification = static::fnLandlineVerification($telephoneNumber, $visitor_id);
                                if ($result_verification == "Success") {
                                    $result_type = "LANDLINE";
                                }
                            }
                        }
                    } else if ($str_verify_result != 'Invalid') {    //Update : 06/09/2013 : Phone verification update
                        if (($strPhoneType == 'Mobile' || $strPhoneType == '') && (substr($telephoneNumber, 0, 2) == '07' || substr($telephoneNumber, 0, 1) == '7')) { //MOBILE
                            $result_verification = static::fnMobileVerification($telephoneNumber, $visitor_id);
                            if ($result_verification == "Success") {
                                $result_type = "MOBILE";
                            }
                        } else if (($strPhoneType == 'Landline' || $strPhoneType == '') && (substr($telephoneNumber, 0, 2) != '07' && substr($telephoneNumber, 0, 1) != '7')) {  //LANDLINE
                            $result_verification = static::fnLandlineVerification($telephoneNumber, $visitor_id);
                            if ($result_verification == "Success") {
                                $result_type = "LANDLINE";
                            }
                        }
                    } else if ($str_verify_result == 'Invalid') {
                        $result_verification = "Error";
                    } else {
                        $result_verification = "Error";
                    }

                    $result_detail = "TelephoneNumber:" . $str_verify_tel . "||ValidationResult:" . $str_verify_result . "||ValidationLevel:" . $str_verify_level . "||NumberType:" . $str_verify_numType . "||Location:" . $str_verify_location . "||Provider:" . $str_verify_provider . "||CountryCode:" . $str_verify_countryCode . "||CountryName:" . $str_verify_country;
                }
            }

            $str_verify_result = (($str_verify_result == 'Invalid') ? "Error" : "Success");
            $phonetrack = new ValidationPhoneTracking;
            $phonetrack->visitor_id = $visitor_id;
            $phonetrack->phone_number = $telephoneNumber;
            $phonetrack->validation_type = 'InternationalTelephoneValidation';
            $phonetrack->validation_result = $str_verify_result;
            $phonetrack->validation_result_details = $result_detail;
            $phonetrack->save();
            return $result_verification;
        } catch (SOAPFault $e) {
        }
    }
    /**
     * Fn mobile verification
     *
     * @param $number
     * @param $visitor_id
     * @return string
     * @throws \SoapFault
     */
    public function fnMobileVerification($number, $visitor_id)
    {
        $result_verification = "Error";
        $options = 'UseLineValidation';

        $params = array(
            "username" => config('constants.DATA8_USERNAME'),
            "password" => config('constants.DATA8_PASSWORD'),
            "number" => $number,
            "options" => $options
        );
        // Setting read timeout in seconds
        ini_set('default_socket_timeout', 10);

        // Setting request timeout in seconds as an option
        $client = new \SoapClient("https://webservices.data-8.co.uk/MobileValidation.asmx?WSDL", array('connection_timeout' => 10));
        // Try block starts
        try {
            $xml_mobile = $client->IsValid($params);

            $arrResult = json_decode(json_encode((array) $xml_mobile), 1);

            $arrValidStatus     = $arrResult['IsValidResult']['Status'];

            $int_result         = $arrValidStatus['Success'];  //1,0 : check or not at service
            $int_credit_rem     = $arrValidStatus['CreditsRemaining']; //Remaining credit
            $str_ver_result     = $arrResult['IsValidResult']['Result'];  //Success,Invalid
            $str_country         = $arrResult['IsValidResult']['CountryISO'];
            $str_org             = $arrResult['IsValidResult']['Organisation'];
            $str_network         = $arrResult['IsValidResult']['NetworkName'];
            $str_networkType     = $arrResult['IsValidResult']['NetworkType'];
            $str_port             = $arrResult['IsValidResult']['Ported'];
            $str_portOrg         = $arrResult['IsValidResult']['PortedFromOrganisation'];
            $str_portNetwork     = $arrResult['IsValidResult']['PortedFromNetwork'];
            $str_location         = $arrResult['IsValidResult']['LocationISO'];

            if ($int_credit_rem == 100 || $int_credit_rem == 50 || $int_credit_rem == 25) {
                $this->emailRepo->fnSendGeneralMailAWS("Phone Verification", "<p>Function : fnMobileVerification() <br>Internation Telephone Validation >>  " . $int_credit_rem . " Remaining</p>");
            }
            if ($int_credit_rem <= 0) {    //Credit Finish. Bipass
                $str_ver_result = "SKIP";
                $result_verification = "SUCCESS";
                $str_res_detail = "";
            } else {
                if ($int_result == 0) {
                    $result_verification = "Error";
                    $str_res_detail = "ErrorMessage:" . $arrValidStatus['ErrorMessage'];
                } else {
                    $result_verification = (($str_ver_result == 'Invalid' || $str_ver_result == 'Error') ? "Error" : "Success");
                    $str_res_detail = "CountryISO:" . $str_country . "||Organisation:" . $str_org . "||NetworkName:" . $str_network . "||NetworkType:" . $str_networkType . "||Ported:" . $str_port . "||PortedFromOrganisation:" . $str_portOrg . "||PortedFromNetwork:" . $str_portNetwork . "||LocationISO:" . $str_location . "||Result:" . $str_ver_result;
                }
            }
            $phonetrack = new ValidationPhoneTracking;
            $phonetrack->visitor_id = $visitor_id;
            $phonetrack->phone_number = $number;
            $phonetrack->validation_type = 'MobileValidation';
            $phonetrack->validation_result = $str_ver_result;
            $phonetrack->validation_result_details = $str_res_detail;
            $phonetrack->save();
            return $result_verification;
        } catch (SOAPFault $e) { // Catch block starts
            if (strpos($e->getMessage(), 'Error Fetching http headers') !== false) {
                $this->emailRepo->fnSendGeneralMailAWS("Phone Verification Timeout", "<p>Function : fnMobileVerification() <br>Telephone Line Validation Timeout. \n VisitorId : '" . $visitor_id . "'\n Status : '" . $e->getMessage() . "'>></p>");

                if (!preg_match("/^(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/", $number)) {
                    $result_verification = "Error";
                } else {
                    $result_verification = "Success";
                }
                $strFileContent = "\n----------\nDate: " . date('Y-m-d H:i:s') . "\nMsg : server side validation \n Report:" . $result_verification . " \n Landline : " . $number . " \n Visitor ID : " . $visitor_id . " \n From : Web";
                $logWrite           =  $this->logRepo->writeLog('-server_side_mobile_validation', $strFileContent);
                return $result_verification;
            }
        }
    }
    /**
     * Fn land line verification
     *
     * @param $number
     * @param $visitor_id
     * @return string
     * @throws \SoapFault
     */
    public function fnLandlineVerification($number, $visitor_id)
    {
        $result_verification = "Error";
        $options = array();
        $params = array(
            "username" => config('constants.DATA8_USERNAME'),
            "password" => config('constants.DATA8_PASSWORD'),
            "number" => $number,
            "options" => $options
        );

        // Setting read timeout in seconds
        ini_set('default_socket_timeout', 10);

        // Setting request timeout in seconds as an option
        $client = new SoapClient("https://webservices.data-8.co.uk/TelephoneLineValidation.asmx?WSDL", array('connection_timeout' => 10));

        // Try block starts
        try {
            $xml_telephone = $client->IsValid($params);

            $arrResult = json_decode(json_encode((array) $xml_telephone), 1);

            $int_result = $arrResult['IsValidResult']['Status']['Success'];   //1,0 : check or not at service
            $int_credit_rem = $arrResult['IsValidResult']['Status']['CreditsRemaining']; //Remaining credit

            $str_ver_result = $arrResult['IsValidResult']['Result'];      //Valid,Invalid

            $str_res_detail = "";
            if ($int_credit_rem == 100 || $int_credit_rem == 50 || $int_credit_rem == 25) {
                $this->emailRepo->fnSendGeneralMailAWS("Phone Verification", "<p>Function : fnLandlineVerification() <br>Telephone Validation >>  " . $int_credit_rem . " Remaining</p>");
            }

            if ($int_credit_rem <= 0) {    //Credit Finish. Bipass
                $str_ver_result = "SKIP";
                $result_verification = "SUCCESS";
            } else {
                if ($int_result == 0) {
                    $result_verification = "Error";
                    $str_res_detail = "ErrorMessage:" . $arrResult['IsValidResult']['Status']['ErrorMessage'];
                } else {
                    $result_verification = (($str_ver_result == 'Invalid' || $str_ver_result == 'Error') ? "Error" : "Success");
                }
            }

            $phonetrack = new ValidationPhoneTracking;

            $phonetrack->visitor_id = $visitor_id;
            $phonetrack->phone_number = $number;
            $phonetrack->validation_type = 'TelephoneLineValidation';
            $phonetrack->validation_result = $str_ver_result;
            $phonetrack->validation_result_details = $str_res_detail;

            $phonetrack->save();

            return $result_verification;
        } catch (SOAPFault $e) { // Catch block starts
            if (strpos($e->getMessage(), 'Error Fetching http headers') !== false) {
                $this->emailRepo->fnSendGeneralMailAWS("Phone Verification Timeout", "<p>Function : fnLandlineVerification() <br>Telephone Line Validation Timeout. \n VisitorId : '" . $visitor_id . "'\n Status : '" . $e->getMessage() . "'>></p>");

                if (!preg_match("/^(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/", $number)) {
                    $result_verification = "Error";
                } else {
                    $result_verification = "Success";
                }

                $strFileContent = "\n----------\nDate: " . date('Y-m-d H:i:s') . "\nMsg : server side validation \n Report:" . $result_verification . " \n Landline : " . $number . " \n Visitor ID : " . $visitor_id . " \n From : Web";
                $logWrite           = $this->logRepo->writeLog('-server_side_landline_validation', $strFileContent);

                return $result_verification;
            }
        }
    }
    /**
     * Check valid email
     *
     * @param $email
     * @param $intVisitorId
     * @return bool
     */
    public function CheckValidEmail($email, $intVisitorId)
    {
        $today_date = date('Y-m-d');

        $userdetails = User::where('email', $email)
            ->where('users.record_status', 'LIVE')
            ->select('users.id', 'users.email', 'users.telephone')->first();

        if (isset($userdetails) && $userdetails->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Check number verify api
     *
     * @param $phone_number
     * @param $visitor_id
     * @return mixed
     */
    public function CheckNumverifyApi($phone_number, $visitor_id)
    {
        $access_key = '6585ec278c3976e9f689d125265d4712';
        // Initialize CURL:
        $country_code = 'US';
        $ch = curl_init('http://apilayer.net/api/validate?access_key=' . $access_key . '&number=' . $phone_number . '&country_code=' . $country_code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);
        $validationResult = json_decode($json, true);
        $resultStatus = '';
        if (isset($validationResult['valid']) && $validationResult['valid'] == 1) {
            $resultStatus = 'Valid';
        } else {
            $resultStatus = 'Error';
        }
        $checkPhonetracking = $this->checkPhonetracking($phone_number, $resultStatus);
        if ($checkPhonetracking == 0) {
            $phonetrack = new ValidationPhoneTracking;
            $phonetrack->visitor_id = $visitor_id;
            $phonetrack->phone_number = $phone_number;
            $phonetrack->validation_type = isset($validationResult['line_type']) ? $validationResult['line_type'] : '';
            $phonetrack->validation_result = $resultStatus;
            $phonetrack->validation_result_details = json_encode($validationResult);
            $phonetrack->save();
        }
        $request = [
            'access_key' => $access_key,
            'phone_number' => $phone_number,
            'country_code' => 'US'
        ];
        $url = 'http://apilayer.net/api/validate';
        $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n visitor_id : " . $visitor_id . " \n request :" . json_encode($request, JSON_UNESCAPED_SLASHES) . " \n url :" . $url . "\n response :" . json_encode($validationResult, JSON_UNESCAPED_SLASHES);
        $logWrite   = $this->logRepo->writeLog('-numverify_api', $strFileContent);
        return $validationResult;
    }
    /**
     * Fn is valid email
     *
     * @param $email
     * @param $intVisitorId
     * @return mixed|string
     */
    public function fnIsValidEmail($email, $intVisitorId)
    {
        if (substr_count($email, "@922.com") > 0 || substr_count($email, "@911.com") > 0)
            return "valid";
        $result_verify = "invalid";
        // URL  PARAMETERS
        $type = "json"; # XML/JSON
        $api_key = "1000623-FB855474";
        $domain = "unfairfees.co.uk";
        $postback_URL = "http://www.xverify.com/services/emails/verify/?email=$email&type=$type&apikey=$api_key&domain=$domain";

        $response = file_get_contents($postback_URL);
        $myArray = json_decode($response, true);
        $result = $myArray['email']['status'];
        $result_details = "";
        $resSelect     =    ValidationEmailTracking::select('id')
            ->where('validated_email', '=', $email)
            ->where('result', '=', $result)
            ->get();
        if ($resSelect->count() == 0) {
            $phonetrack = new ValidationEmailTracking;

            $phonetrack->visitor_id = $intVisitorId;
            $phonetrack->validated_email = $email;
            $phonetrack->result = $result;
            $phonetrack->result_details = $result_details;
            $phonetrack->validated_date = date('Y-m-d H:i:s');

            $phonetrack->save();
        }
        return $result;
    }
    /**
     * Check phone duplicate
     *
     * @param $strTelephone
     * @return bool
     */
    public function checkPhoneDuplicate($strTelephone)
    {
        $today_date = date('Y-m-d');

        $userdetails = User::where('telephone', $strTelephone)
            ->where('users.record_status', 'LIVE')
            ->select('users.id', 'users.email', 'users.telephone')->first();

        if (isset($userdetails) && $userdetails->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Is valid post code old
     *
     * @param $strPostcode
     * @return string
     * @throws \Exception
     */
    public function isValidPostcodeOld($strPostcode)
    {
        $bool_isLive         =    1;
        $str_simplyserver     =    "http://www.simplylookupadmin.co.uk/xmlservice";
        $XMLData             =    $str_address = "";
        $datakey              =      $this->commonFunctionRepo->getDataKey();
        if ($bool_isLive == '1') {   //Live
            $XMLService = $str_simplyserver . "/SearchForThoroughfareAddress.aspx?datakey=" . $datakey . "&postcode=" . $strPostcode . "&AppID=20";
        } else { //Test
            $XMLService = $str_simplyserver . "/SearchForThoroughfareAddress.aspx?datakey=" . DATA_KEY_TEST . "&postcode=" . $strPostcode . "&AppID=20";
        }
        // Function call for get post code details
        $arrResultDetail     =      $this->commonFunctionRepo->fileGetContent($XMLService, "Postcode_lookup");
        $strResult             =    $arrResultDetail['result'];
        $strXml             =    $arrResultDetail['result_detail'];
        // Create XML Object
        $objXml             =    (array)new \SimpleXMLElement($strXml);
        // Convert xml into array
        $arr_postcode_res = json_decode(json_encode($objXml), true);
        $str_postcode_verify = $arr_postcode_res['confirm'];    //yes,No
        $str_credit_text = $arr_postcode_res['credits_display_text'];
        if (!is_array($str_credit_text)) {
            //To identify credits limit we need to replace all strings from credits_display_text
            $int_RemainCredit = (int)str_replace(array(' Credits', 'Web Thoroughfare ', 'Universal Thoroughfare ', ','), '', $str_credit_text);
        } else {
            $int_RemainCredit = 0;
        }
        if ($int_RemainCredit == 100 || $int_RemainCredit == 50 || $int_RemainCredit == 25) {
        }
        $str_credits_display_text   = '';
        $str_lookup_id              = '';
        $str_organisation           = '';
        $str_line1                  = '';
        $str_line2                  = '';
        $str_line3                  = '';
        $str_town                   = '';
        $str_county                 = '';
        $str_country                = '';
        $str_deliverypointsuffix    = '';
        $str_nohouseholds           = '';
        $str_smallorg               = '';
        $str_pobox                  = '';
        $str_rawpostcode            = '';
        $str_pz_mailsort            = '';
        $str_spare                  = '';
        $str_udprn                  = '';
        $str_fl_unique              = '';
        if ($int_RemainCredit <= 0 && count($str_credit_text) > 0) {    //Credit Finish. Bipass
            $result['status'] = "SKIP";
            $result['postal_result'] = "SUCCESS";
        } else {
            if (!array_key_exists('message', $arr_postcode_res)) {  //If invalid "message" field is in result
                $result['status'] = "VALID";
                $result['postal_result'] = "SUCCESS";

                $str_credits_display_text   = (empty($arr_postcode_res['credits_display_text']) ? "" : $arr_postcode_res['credits_display_text']);
                $str_lookup_id              = (empty($arr_postcode_res['record']['id']) ? "" : $arr_postcode_res['record']['id']);
                $str_organisation           = (empty($arr_postcode_res['record']['organisation']) ? "" : $arr_postcode_res['record']['organisation']);
                $str_line1                  = (empty($arr_postcode_res['record']['line1']) ? "" : $arr_postcode_res['record']['line1']);
                $str_line2                  = (empty($arr_postcode_res['record']['line2']) ? "" : $arr_postcode_res['record']['line2']);
                $str_line3                  = (empty($arr_postcode_res['record']['line3']) ? "" : $arr_postcode_res['record']['line3']);
                $str_town                   = (empty($arr_postcode_res['record']['town']) ? "" : $arr_postcode_res['record']['town']);
                $str_county                 = (empty($arr_postcode_res['record']['county']) ? "" : $arr_postcode_res['record']['county']);
                $str_country                = (empty($arr_postcode_res['record']['country']) ? "" : $arr_postcode_res['record']['country']);
                $str_deliverypointsuffix    = (empty($arr_postcode_res['record']['deliverypointsuffix']) ? "" : $arr_postcode_res['record']['deliverypointsuffix']);
                $str_nohouseholds           = (empty($arr_postcode_res['record']['nohouseholds']) ? "" : $arr_postcode_res['record']['nohouseholds']);
                $str_smallorg               = (empty($arr_postcode_res['record']['smallorg']) ? "" : $arr_postcode_res['record']['smallorg']);
                $str_pobox                  = (empty($arr_postcode_res['record']['pobox']) ? "" : $arr_postcode_res['record']['pobox']);
                $str_rawpostcode            = (empty($arr_postcode_res['record']['rawpostcode']) ? "" : $arr_postcode_res['record']['rawpostcode']);
                $str_pz_mailsort            = (empty($arr_postcode_res['record']['pz_mailsort']) ? "" : $arr_postcode_res['record']['pz_mailsort']);
                $str_spare                  = (empty($arr_postcode_res['record']['spare']) ? "" : $arr_postcode_res['record']['spare']);
                $str_udprn                  = (empty($arr_postcode_res['record']['udprn']) ? "" : $arr_postcode_res['record']['udprn']);
                $str_fl_unique              = (empty($arr_postcode_res['record']['unique']) ? "" : $arr_postcode_res['record']['unique']);
                //line1
                if (is_array($str_line1)) {
                    $str_address .= $str_line1[0];
                } else {
                    $str_address .= (($str_line1 != '') ? $str_line1 . ', ' : '');
                }
                //Town
                if (is_array($str_town)) {
                    $str_address .= $str_town[0];
                } else {
                    $str_address .= (($str_town != '') ? $str_town . ', ' : '');
                }
                //county
                if (is_array($str_county)) {
                    $str_address .= $str_county[0];
                } else {
                    $str_address .= (($str_county != '') ? $str_county . ', ' : '');
                }
                //country
                if (is_array($str_country)) {
                    $str_address .= $str_country[0];
                } else {
                    $str_address .= (($str_country != '') ? $str_country : '');
                }
            } else {
                $result['status'] = "INVALID";
                $result['postal_result'] = "ERROR";
            }
        }
        $postcode_count                 =    PostcodeValidation::select('post_code', 'status')
            ->where('post_code', '=', $strPostcode)
            ->count();
        if ($postcode_count > 0) {
            $postcode = PostcodeValidation::where('post_code', '=', $strPostcode)->first();
            $postcode->post_code             =    $strPostcode;
            $postcode->address                 =    $str_address;
            $postcode->credits_display_text =    $str_credits_display_text;
            $postcode->lookup_id             =    $str_lookup_id;
            $postcode->organisation         =    $str_organisation;
            $postcode->line1                 =    $str_line1;
            $postcode->line2                 =    $str_line2;
            $postcode->line3                 =    $str_line3;
            $postcode->town                 =    $str_town;
            $postcode->county                 =    $str_county;
            $postcode->country                 =    $str_country;
            $postcode->deliverypointsuffix     =    $str_deliverypointsuffix;
            $postcode->nohouseholds         =    $str_nohouseholds;
            $postcode->smallorg             =    $str_smallorg;
            $postcode->pobox                 =    $str_pobox;
            $postcode->rawpostcode             =    $str_rawpostcode;
            $postcode->pz_mailsort             =    $str_pz_mailsort;
            $postcode->spare                 =    $str_spare;
            $postcode->udprn                 =    $str_udprn;
            $postcode->fl_unique             =    $str_fl_unique;
            $postcode->status                 =    $result['status'];
            $postcode->save();
        } else {
            $result['address']                 =     $str_address;
            $postcode                         =    new PostcodeValidation();
            $postcode->post_code             =    $strPostcode;
            $postcode->address                 =    $str_address;
            $postcode->credits_display_text =    $str_credits_display_text;
            $postcode->lookup_id             =    $str_lookup_id;
            $postcode->organisation         =    $str_organisation;
            $postcode->line1                 =    $str_line1;
            $postcode->line2                 =    $str_line2;
            $postcode->line3                 =    $str_line3;
            $postcode->town                 =    $str_town;
            $postcode->county                 =    $str_county;
            $postcode->country                 =    $str_country;
            $postcode->deliverypointsuffix     =    $str_deliverypointsuffix;
            $postcode->nohouseholds         =    $str_nohouseholds;
            $postcode->smallorg             =    $str_smallorg;
            $postcode->pobox                 =    $str_pobox;
            $postcode->rawpostcode             =    $str_rawpostcode;
            $postcode->pz_mailsort             =    $str_pz_mailsort;
            $postcode->spare                 =    $str_spare;
            $postcode->udprn                 =    $str_udprn;
            $postcode->fl_unique             =    $str_fl_unique;
            $postcode->status                 =    $result['status'];
            $postcode->save();
        }
        return $result['status'];
    }
    /**
     * Fn user duplicate check
     *
     * @param $arraParams
     * @param false $flFromCakePosting
     * @return bool
     */
    public function fnUserDuplicateCheck($arraParams, $flFromCakePosting = false)
    {

        $date = ((!isset($arraParams['date']) || empty($arraParams['date'])) ? date('Y-m-d') : $arraParams['date']);
        $ip = ((!isset($arraParams['ip']) || empty($arraParams['ip'])) ? @$_SERVER['REMOTE_ADDR'] : $arraParams['ip']);
        $email = ((!isset($arraParams['email']) || empty($arraParams['email'])) ? "" : $arraParams['email']);
        $phone = ((!isset($arraParams['phone']) || empty($arraParams['phone'])) ? "" : $arraParams['phone']);
        $fName = ((!isset($arraParams['fName']) || empty($arraParams['fName'])) ? "" : $arraParams['fName']);
        $lName = ((!isset($arraParams['lName']) || empty($arraParams['lName'])) ? "" : $arraParams['lName']);

        if ($flFromCakePosting) {
            $arrUser = DB::table('users')
                ->join('buyer_api_responses', 'users.id', "=", 'buyer_api_responses.user_id')
                ->join('buyer_api_response_details', 'buyer_api_responses.id', "=", 'buyer_api_response_details.buyer_api_response_id')
                ->select('users.id')
                ->where('users.record_status', "LIVE")
                ->where('buyer_api_responses.created_at', $date)
                ->where('buyer_api_response_details.lead_value', ">", '0')
                ->where('buyer_api_responses.result', 'Success')
                ->where(function ($q) use ($email, $phone) {
                    $q->where('users.email', $email)
                        ->orWhere('users.telephone', $phone);
                })
                ->get();
        } else {

            $arrUser = DB::table('users')
                ->join('visitors', 'visitors.id', '=', 'users.visitor_id')
                ->select('users.id', 'users.email', 'users.telephone', 'users.first_name', 'users.last_name', 'visitors.ip_address', 'users.created_at')
                ->where('users.record_status', "LIVE")
                ->where(function ($q) use ($email, $fName, $lName, $ip, $phone) {
                    $q->where('users.email', $email)
                        ->orWhere(function ($r) use ($fName, $lName, $ip, $phone) {
                            $r->where('users.first_name', $fName)
                                ->Where('users.last_name', $lName)
                                ->Where('visitors.ip_address', $ip);
                        })
                        ->orWhere('users.telephone', $phone);
                });
            $arrUser = $arrUser->get();
        }

        if (count($arrUser) > 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Get address from postcode
     *
     * @param $strPostcode
     * @param $intVisitorId
     * @return array
     */
    public function get_addressfrom_postcode($strPostcode, $intVisitorId)
    {
        $str_address     = "";
        $result         = array();
        $returnData     = array();
        if ($strPostcode != '') {
            $datakey =   $this->commonFunctionRepo->getDataKey($intVisitorId);
            $str_simplyserver = "http://www.simplylookupadmin.co.uk/xmlservice";
            $XMLData = $str_address = "";
            $XMLService = $str_simplyserver . "/SearchForThoroughfareAddress.aspx?datakey=" . $datakey . "&postcode=" . $strPostcode . "&AppID=20";

            $arrResultDetail    = GeneralClass::fnFileGetContent($XMLService, "Postcode_lookup");
            $strResult             = $arrResultDetail['result'];
            $strXml             = $arrResultDetail['result_detail'];

            // Create XML Object
            $objXml = (array)new SimpleXMLElement($strXml);

            // Convert xml into array
            $arr_postcode_res = json_decode(json_encode($objXml), true);
            $str_postcode_verify = $arr_postcode_res['confirm'];    //yes,No
            $str_credit_text = $arr_postcode_res['credits_display_text'];

            if (!is_array($str_credit_text)) {
                //To identify credits limit we need to replace all strings from credits_display_text
                $int_RemainCredit = (int)str_replace(array(' Credits', 'Web Thoroughfare ', 'Universal Thoroughfare ', ','), '', $str_credit_text);
            } else {
                $int_RemainCredit = 0;
            }

            if ($int_RemainCredit == 100 || $int_RemainCredit == 50 || $int_RemainCredit == 25) {
                $this->emailRepo->fnSendGeneralMailAWS("Phone Verification", "<p>Function : fnIsValidPostcode() <br>Postal Code Validation >>  " . $int_RemainCredit . " Remaining</p>");
            }

            $str_credits_display_text   = '';
            $str_lookup_id              = '';
            $str_organisation           = '';
            $str_line1                  = '';
            $str_line2                  = '';
            $str_line3                  = '';
            $str_town                   = '';
            $str_county                 = '';
            $str_country                = '';
            $str_deliverypointsuffix    = '';
            $str_nohouseholds           = '';
            $str_smallorg               = '';
            $str_pobox                  = '';
            $str_rawpostcode            = '';
            $str_pz_mailsort            = '';
            $str_spare                  = '';
            $str_udprn                  = '';
            $str_fl_unique              = '';

            if ($int_RemainCredit <= 0 && strlen($credit_text) > 0) {
                //Credit Finish. Bipass
                $result['status'] = "SKIP";
                $result['postal_result'] = "SUCCESS";
            } else {
                if (!array_key_exists('message', $arr_postcode_res)) {  //If invalid "message" field is in result
                    $result['status'] = "VALID";
                    $result['postal_result'] = "SUCCESS";

                    $str_credits_display_text   = (empty($arr_postcode_res['credits_display_text']) ? "" : $arr_postcode_res['credits_display_text']);
                    $str_lookup_id              = (empty($arr_postcode_res['record']['id']) ? "" : $arr_postcode_res['record']['id']);
                    $str_organisation           = (empty($arr_postcode_res['record']['organisation']) ? "" : $arr_postcode_res['record']['organisation']);
                    $str_line1                  = (empty($arr_postcode_res['record']['line1']) ? "" : $arr_postcode_res['record']['line1']);
                    $str_line2                  = (empty($arr_postcode_res['record']['line2']) ? "" : $arr_postcode_res['record']['line2']);
                    $str_line3                  = (empty($arr_postcode_res['record']['line3']) ? "" : $arr_postcode_res['record']['line3']);
                    $str_town                   = (empty($arr_postcode_res['record']['town']) ? "" : $arr_postcode_res['record']['town']);
                    $str_county                 = (empty($arr_postcode_res['record']['county']) ? "" : $arr_postcode_res['record']['county']);
                    $str_country                = (empty($arr_postcode_res['record']['country']) ? "" : $arr_postcode_res['record']['country']);
                    $str_deliverypointsuffix    = (empty($arr_postcode_res['record']['deliverypointsuffix']) ? "" : $arr_postcode_res['record']['deliverypointsuffix']);
                    $str_nohouseholds           = (empty($arr_postcode_res['record']['nohouseholds']) ? "" : $arr_postcode_res['record']['nohouseholds']);
                    $str_smallorg               = (empty($arr_postcode_res['record']['smallorg']) ? "" : $arr_postcode_res['record']['smallorg']);
                    $str_pobox                  = (empty($arr_postcode_res['record']['pobox']) ? "" : $arr_postcode_res['record']['pobox']);
                    $str_rawpostcode            = (empty($arr_postcode_res['record']['rawpostcode']) ? "" : $arr_postcode_res['record']['rawpostcode']);
                    $str_pz_mailsort            = (empty($arr_postcode_res['record']['pz_mailsort']) ? "" : $arr_postcode_res['record']['pz_mailsort']);
                    $str_spare                  = (empty($arr_postcode_res['record']['spare']) ? "" : $arr_postcode_res['record']['spare']);
                    $str_udprn                  = (empty($arr_postcode_res['record']['udprn']) ? "" : $arr_postcode_res['record']['udprn']);
                    $str_fl_unique              = (empty($arr_postcode_res['record']['unique']) ? "" : $arr_postcode_res['record']['unique']);
                    //line1
                    if (is_array($str_line1)) {
                        $str_address .= $str_line1[0];
                    } else {
                        $str_address .= (($str_line1 != '') ? $str_line1 . ', ' : '');
                    }
                    //Town
                    if (is_array($str_town)) {
                        $str_address .= $str_town[0];
                    } else {
                        $str_address .= (($str_town != '') ? $str_town . ', ' : '');
                    }
                    //county
                    if (is_array($str_county)) {
                        $str_address .= $str_county[0];
                    } else {
                        $str_address .= (($str_county != '') ? $str_county . ', ' : '');
                    }
                    //country
                    if (is_array($str_country)) {
                        $str_address .= $str_country[0];
                    } else {
                        $str_address .= (($str_country != '') ? $str_country : '');
                    }
                } else {
                    $result['status'] = "INVALID";
                    $result['postal_result'] = "ERROR";
                }
            }
            $result['address'] = $str_address;
            $returnData = array();
            $returnData["credits_display_text"] = $str_credits_display_text;
            $returnData["lookup_id"] = $str_lookup_id;
            $returnData["organisation"] = $str_organisation;
            $returnData["line1"] = $str_line1;
            $returnData["line2"] = $str_line2;
            $returnData["line3"] = $str_line3;
            $returnData["town"] = $str_town;
            $returnData["county"] = $str_county;
            $returnData["country"] = $str_country;
            $returnData["deliverypointsuffix"] = $str_deliverypointsuffix;
            $returnData["nohouseholds"] = $str_nohouseholds;
            $returnData["smallorg"] = $str_smallorg;
            $returnData["pobox"] = $str_pobox;
            $returnData["rawpostcode"] = $str_rawpostcode;
            $returnData["pz_mailsort"] = $str_pz_mailsort;
            $returnData["spare"] = $str_spare;
            $returnData["udprn"] = $str_udprn;
            $returnData["fl_unique"] = $str_fl_unique;
            //Check whether post code information is available in DB
            $arrPostcodeInfo = PostcodeValidation::where('post_code', '=', $strPostcode)
                ->select('id')
                ->first();
            if (count($arrPostcodeInfo) > 0) {
                $postcode_data = array(
                    'address'       => $str_address,
                    'credits_display_text' => $str_credits_display_text,
                    'lookup_id'            => $str_lookup_id,
                    'organisation'         => $str_organisation,
                    'line1'                   => $str_line1,
                    'line2'                   => $str_line2,
                    'line3'                   => $str_line3,
                    'town'                   => $str_town,
                    'county'               => $str_county,
                    'country'              => $str_country,
                    'deliverypointsuffix'  => $str_deliverypointsuffix,
                    'nohouseholds'         => $str_nohouseholds,
                    'smallorg'               => $str_smallorg,
                    'pobox'                => $str_pobox,
                    'rawpostcode'          => $str_rawpostcode,
                    'pz_mailsort'          => $str_pz_mailsort,
                    'spare'                => $str_spare,
                    'udprn'                => $str_udprn,
                    'fl_unique'            => $str_fl_unique
                );

                PostcodeValidation::where('id', '=', $arrPostcodeInfo->id)->update($postcode_data);
            } else {
                $date = date('Y-m-d H:i:s');
                $postcode_data = array(
                    'address'       => $str_address,
                    'credits_display_text' => $str_credits_display_text,
                    'lookup_id'            => $str_lookup_id,
                    'organisation'         => $str_organisation,
                    'line1'                   => $str_line1,
                    'line2'                   => $str_line2,
                    'line3'                   => $str_line3,
                    'town'                   => $str_town,
                    'county'               => $str_county,
                    'country'              => $str_country,
                    'deliverypointsuffix'  => $str_deliverypointsuffix,
                    'nohouseholds'         => $str_nohouseholds,
                    'smallorg'               => $str_smallorg,
                    'pobox'                => $str_pobox,
                    'rawpostcode'          => $str_rawpostcode,
                    'pz_mailsort'          => $str_pz_mailsort,
                    'spare'                => $str_spare,
                    'udprn'                => $str_udprn,
                    'fl_unique'            => $str_fl_unique,
                    'status'               => $result['status'],
                    'created_at'           => $date
                );
                $insert = PostcodeValidation::insertGetId($postcode_data);
            }
        }
        // Write the contents back to the file
        $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n postcode : $strPostcode \n intVisitorId : $intVisitorId \n Address: " . $str_address . " \n result : " . serialize($result) . " \n returnData : " . serialize($returnData) . " \n ";
        $logWrite           = $this->logRepo->writeLog('-get_addressfrom_postcode', $strFileContent);

        return $returnData;
    }
}
