<?php

namespace App\Repositories;

use App\Models\DeviceSiteMaster;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\BrowserDetectionRepository;
use App\Repositories\MobileDetectRepository;
use App\Repositories\Interfaces\UAInterface;

/**
 * Class UARepository
 *
 * @package App\Repositories
 */
class UARepository implements UAInterface
{
    public $useragent;

    /**
     * UARepository constructor.
     */
    public function __construct()
    {
        $this->commonFunctionRepo = new CommonFunctionsRepository();
        $this->browserDetection = new BrowserDetectionRepository();
        $this->mobileDetect = new MobileDetectRepository();
    }
    /**
     * Parser user agent
     *
     * @param null $u_agent
     * @return mixed
     */
    public function parse_user_agent($u_agent = null)
    {
        $arrUseragent = [];
        $this->getFromMobileDetect($u_agent);
        $this->getFromBrowserDetection($u_agent);
        $arrUseragent = $this->getFromParseUserAgent($u_agent);
        if (empty($this->useragent['browser'])) {
            $this->useragent['browser'] = $arrUseragent['browser'];
        } elseif (strtolower($this->useragent['browser']) == 'chrome' && strtolower($this->useragent['browser']) != strtolower($arrUseragent['browser'])) {
            $this->useragent['browser'] = $arrUseragent['browser'];
        }
        // Assign platform and browser version
        $this->useragent['platform'] = $arrUseragent['platform'];
        $this->useragent['browserVersion'] = $arrUseragent['version'];
        $this->getCountryUsingGEOIP();
        $this->getSiteFlagFromSiteFlagMaster();

        return $this->useragent;
    }
    /**
     * Get form parse user agent
     *
     * @param null $u_agent
     * @return array|null[]
     */
    public function getFromParseUserAgent($u_agent = null)
    {
        if (is_null($u_agent)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $u_agent = $_SERVER['HTTP_USER_AGENT'];
            }
        }
        $platform = null;
        $browser = null;
        $version = null;
        $empty = array(
            'platform' => $platform,
            'browser' => $browser,
            'version' => $version
        );
        if (!$u_agent) {
            if (empty($empty['browser'])) {
                $empty['browser'] = 'Unknown Browser';
            }
            return $empty;
        }

        if (preg_match('/\((.*?)\)/im', $u_agent, $parent_matches)) {
            preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|(Open|Net|Free)BSD|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS|Switch)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);
            $priority = array(
                'Xbox One',
                'Xbox',
                'Windows Phone',
                'Tizen',
                'Android',
                'FreeBSD',
                'NetBSD',
                'OpenBSD',
                'CrOS',
                'X11'
            );
            $result['platform'] = array_unique($result['platform']);
            if (count($result['platform']) > 1) {
                if ($keys = array_intersect($priority, $result['platform'])) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif (isset($result['platform'][0])) {
                $platform = $result['platform'][0];
            }
        }
        if ($platform == 'linux-gnu' || $platform == 'X11') {
            $platform = 'Linux';
        } elseif ($platform == 'CrOS') {
            $platform = 'Chrome OS';
        }
        preg_match_all(
            '%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
                TizenBrowser|(?:Headless)?Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|Puffin|SamsungBrowser|
                Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
                Valve\ Steam\ Tenfoot|
                NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
                (?:\)?;?)
                (?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
            $u_agent,
            $result,
            PREG_PATTERN_ORDER
        );
        // If nothing matched, return null (to avoid undefined index errors)
        if (!isset($result['browser'][0]) || !isset($result['version'][0])) {
            if (preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result)) {
                if (empty($result['browser'])) {
                    $result['browser'] = 'Unknown Browser';
                }
                return array(
                    'platform' => $platform ?: null,
                    'browser' => $result['browser'],
                    'version' => isset($result['version']) ? $result['version'] ?: null : null
                );
            }
            if (empty($empty['browser'])) {
                $empty['browser'] = 'Unknown Browser';
            }
            return $empty;
        }
        if (preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $u_agent, $rv_result)) {
            $rv_result = $rv_result['version'];
        }
        $browser = $result['browser'][0];
        $version = $result['version'][0];
        $lowerBrowser = array_map('strtolower', $result['browser']);
        $find = function ($search, &$key, &$value = null) use ($lowerBrowser) {
            $search = (array)$search;
            foreach ($search as $val) {
                $xkey = array_search(strtolower($val), $lowerBrowser);
                if ($xkey !== false) {
                    $value = $val;
                    $key = $xkey;

                    return true;
                }
            }
            return false;
        };
        $key = 0;
        $val = '';
        if ($browser == 'Iceweasel' || strtolower($browser) == 'icecat') {
            $browser = 'Firefox';
        } elseif ($find('Playstation Vita', $key)) {
            $platform = 'PlayStation Vita';
            $browser = 'Browser';
        } elseif ($find(array('Kindle Fire', 'Silk'), $key, $val)) {
            $browser = $val == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result['version'][$key]) || !is_numeric($version[0])) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif ($find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS') {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif ($find('Kindle', $key, $platform)) {
            $browser = $result['browser'][$key];
            $version = $result['version'][$key];
        } elseif ($find('OPR', $key)) {
            $browser = 'Opera Next';
            $version = $result['version'][$key];
        } elseif ($find('Opera', $key, $browser)) {
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif ($find('Puffin', $key, $browser)) {
            $version = $result['version'][$key];
            if (strlen($version) > 3) {
                $part = substr($version, -2);
                if (ctype_upper($part)) {
                    $version = substr($version, 0, -2);
                    $flags = array(
                        'IP' => 'iPhone',
                        'IT' => 'iPad',
                        'AP' => 'Android',
                        'AT' => 'Android',
                        'WP' => 'Windows Phone',
                        'WT' => 'Windows'
                    );
                    if (isset($flags[$part])) {
                        $platform = $flags[$part];
                    }
                }
            }
        } elseif ($find(array(
                'IEMobile',
                'Edge',
                'Midori',
                'Vivaldi',
                'SamsungBrowser',
                'Valve Steam Tenfoot',
                'Chrome',
                'HeadlessChrome')
            , $key, $browser)) {
            $version = $result['version'][$key];
        } elseif ($rv_result && $find('Trident', $key)) {
            $browser = 'MSIE';
            $version = $rv_result;
        } elseif ($find('UCBrowser', $key)) {
            $browser = 'UC Browser';
            $version = $result['version'][$key];
        } elseif ($find('CriOS', $key)) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif ($browser == 'AppleWebKit') {
            if ($platform == 'Android') {
                // $key = 0;
                $browser = 'Android Browser';
            } elseif (strpos($platform, 'BB') === 0) {
                $browser = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif ($platform == 'BlackBerry' || $platform == 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } else {
                $find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
            }
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif ($pKey = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser']))) {
            $pKey = reset($pKey);
            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $pKey);
            $browser = 'NetFront';
        }
        if (empty($browser)) {
            $browser = 'Unknown Browser';
        }

        return array('platform' => $platform ?: null, 'browser' => $browser ?: null, 'version' => $version ?: null);
    }
    /**
     * Get from mobile detect
     *
     * @param null $u_agent
     */
    public function getFromMobileDetect($u_agent = null)
    {
        //   $detect = new MobileDetect;
        if ($this->mobileDetect->isMobile($u_agent) && $this->mobileDetect->isTablet($u_agent)) {
            //Tablet
            $device = 'Tablet';
        } elseif ($this->mobileDetect->isMobile($u_agent) && !$this->mobileDetect->isTablet($u_agent)) {
            //mobile
            $device = 'Mobile';
        } else {
            //Web
            $device = 'Web';
        }
        $this->useragent['device'] = $device;
    }
    /**
     * Get from browser detection
     */
    public function getFromBrowserDetection()
    {
        // Create object of browser detection library
        // Set custom rule
        $this->browserDetection->addCustomBrowserDetection('Amazon Silk', 'Silk', true);
        //Parse user agent
        $this->browserDetection->setUserAgent();
        //identify browser
        $this->useragent['browser'] = $this->browserDetection->getName();
        if ($this->browserDetection->getPlatformVersion() == 'unknown') {
            $this->useragent['osFull'] = $this->browserDetection->getPlatform();
        } else {
            $this->useragent['osFull'] = $this->browserDetection->getPlatformVersion();
        }
    }
    /**
     * Get country using GEOIP
     *
     * @param null $ip
     */
    public function getCountryUsingGEOIP($ip = null)
    {
        // If IP address is empty
        if (is_null($ip)) {
            $ip = $this->commonFunctionRepo->get_client_ip();
        }
        // If site access from localhost or function not exist
        if ($_SERVER['HTTP_HOST'] == 'localhost' || !function_exists('geoip_country_code_by_name')) {
            // For localhost
            if ($ip == '::1' || $ip == '127.0.0.1') {
                $countryCode = 'IN';
            } else {
                $countryCode = 'GB';
            }
        } else {
            //Function call for identify country code from IP
            $countryCode = geoip_country_code_by_name($ip);
        }
        //if country code is empty then set it as GB
        if (empty($countryCode)) {
            $countryCode = 'GB';
        }
        $this->useragent['country'] = $countryCode;
    }
    /**
     * Get site flag from site flag master
     *
     * @param null $strSiteFlag
     */
    public function getSiteFlagFromSiteFlagMaster($strSiteFlag = null)
    {
        if (is_null($strSiteFlag)) {
            $arrUserAgentInfo = $this->useragent;
            $strSiteFlag = $arrUserAgentInfo['device'];
        }
        //Defince default value of site flag Id.
        $intSiteFlagId = 0;
        $SiteFlagMaster = DeviceSiteMaster::where('device_site_name', $strSiteFlag)->first();
        if (!empty($SiteFlagMaster)) {
            $intSiteFlagId = $SiteFlagMaster->id;
        }
        $this->useragent['siteFlagId'] = $intSiteFlagId;
    }
}
