<?php 

namespace App\Repositories\Interfaces;

interface DynamicUrlInterface
{
	public static function stringcrypt( $string, $action );
	public function trackerType_Vendor( $acsrcId );
	public function generateRedirectlink( $urlgenerateParam );
	public static function strpos_arr( $haystack, $needle );
	public static  function getRedirectionURL($arrUrlParams);
	public function getLPLink( $linkId );
	public function getCustomURLFromDB( $findParam );
	public function getRandomURL($array, $numerical_array);
}