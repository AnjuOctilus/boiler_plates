<?php 

namespace App\Repositories\Interfaces;

interface CommonFunctionsInterface 
{
    public function stringcrypt($string,$action);
    public function getCampaignAffID($strCampaign = '', $intSiteFlagId, $intHoOfferId = '');
	public function checkAffiliatePixel($campaignId, $aff_id, $offer_id);
	public static function fnGetConfigValue($strConfigParam);
	public function creatURL($fileName, $arrValues);
	public function base64url_encode($data);
	public function fileGetContent($strUrl, $logType = '', $method = 'get', $arrPostFields = array());
    public function getDataKey($visitor_id=0);
    public function isTestLiveEmail($email);
    public function getPostingLeadBuyer();
	public function getLeadBuyerID($strLeadBuyerName = 'CAKE'); 
	public function getDdMmYyyy($date);
	public function get_client_ip();
    public function changeDateFormat($strFormat, $strDate = '00-00-0000');
	public function convertXmlToArray($xml, $main_heading = '');
	public function dynamicAdvertorialsAdd($strFileName,$splitPath,$domian_name);
    public function getAdvertorialIdFromName($strAdvName, $intSiteFlagId); 
    public function getTrackerType($trackerId);
    public function getDomainId($domain_name = NULL);
	public function sendSMS($recipient,$content);
	public function getStatusofSMS($msgid);
    public function fnConvertXmlToArray($xml, $main_heading = '');
    public function sendEmail($recipient,$content,$subject);
    public function getSplitIdFromName($strSplitName, $intSiteFlagId);
    public function dynamicSplitAddNew($strFileName, $splitPath,$domainName);
}