<?php 

namespace App\Repositories\Interfaces;

interface MobileDetectInterface
{

    public static function getScriptVersion();
    public function setHttpHeaders($httpHeaders = null);
    public function getHttpHeaders();
    public function getHttpHeader($header);
    public function getMobileHeaders();
    public function getUaHttpHeaders();
    public function setCfHeaders($cfHeaders = null);
    public function getCfHeaders();
    public function setUserAgent($userAgent = null);
    public function getUserAgent();
    public function setDetectionType($type = null);
    public function mobileGrade();
    public function prepareVersionNo($ver);
    public static function getProperties();
    public function isMobile($userAgent = null, $httpHeaders = null);
    public function isTablet($userAgent = null, $httpHeaders = null);
    public function checkHttpHeadersForMobile();
    public function match($regex, $userAgent = null);

}