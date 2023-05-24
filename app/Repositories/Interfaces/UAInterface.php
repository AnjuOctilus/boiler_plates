<?php 

namespace App\Repositories\Interfaces;

interface UAInterface
{
	public function parse_user_agent($u_agent);
	public function getFromBrowserDetection();
	public function getFromMobileDetect();
	public function getFromParseUserAgent();
	public function getCountryUsingGEOIP();
	public function getSiteFlagFromSiteFlagMaster();
}