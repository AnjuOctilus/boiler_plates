<?php 

namespace App\Repositories\Interfaces;

interface BrowserDetectionInterface
{
	public function addCustomBrowserDetection($browserName,$isMobile,$isRobot,$separator,$uaNameFindWords);
	public function compareVersions($sourceVer, $compareVer);
	public function getName();
	public function getIECompatibilityView($asArray);
	public function getPlatform();
	public function getPlatformVersion($returnVersionNumbers, $returnServerFlavor);
    public function getUserAgent();
    public function getVersion();
    public function is64bitPlatform();
    public function isChromeFrame();
	public function isInIECompatibilityView();
	public function isMobile();
	public function isRobot();
	public function setUserAgent($agentString);
}