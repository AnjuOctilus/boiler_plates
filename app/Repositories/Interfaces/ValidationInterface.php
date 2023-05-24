<?php 

namespace App\Repositories\Interfaces;

interface ValidationInterface
{
	public function checkPostalCodeTpApi($strPostcode);
	public function CheckValidPhoneNumberApi($strTelephone, $intVisitorId);
	public function fnLocalPhoneValidation($str_phone, $strPhoneType);
	public function fnPhoneVerification($telephoneNumber, $visitor_id, $strPhoneType = '');
	public function fnMobileVerification($number, $visitor_id);
	public function fnLandlineVerification($number, $visitor_id);
	public function CheckValidEmail($email,$intVisitorId);
	public function fnIsValidEmail($email,$intVisitorId);
	public function checkPhoneDuplicate($strTelephone);
	public function fnUserDuplicateCheck($arraParams, $flFromCakePosting);
	public function get_addressfrom_postcode($strPostcode, $intVisitorId);
}	
