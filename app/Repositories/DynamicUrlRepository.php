<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\LaboratoryMediabuysvendor;
use DB;
use App\Repositories\DynamicUrlRepository;
use App\Repositories\Interfaces\DynamicUrlInterface;
/**
 * Class DynamicUrlRepository
 *
 * @package App\Repositories
 */
class DynamicUrlRepository implements DynamicUrlInterface
{
    /**
     * String crypt
     *
     * @param $string
     * @param $action
     * @return false|string
     */
    public static function stringcrypt($string, $action)
    {
        $secret_key = 'C]^82-<L';
        $secret_iv = '4Z[F!^EB';

        $output = false;
        $encrypt_method = 'AES-256-CBC';
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
     * Tracker type vendor
     *
     * @param $acsrcId
     * @return array
     */
    function trackerType_Vendor($acsrcId)
    {
        $tracker_type = 'ADTOPIA';
        $retArray['atp_vendor'] = '';
        $retArray['tracker_type'] = 'ADTOPIA';
        return $retArray;
    }
    /**
     * Generate redirect link
     *
     * @param $urlgenerateParam
     * @return array|string|string[]
     */
    function generateRedirectlink($urlgenerateParam)
    {
        extract($urlgenerateParam);
        if (isset ($url)) {
            if (isset ($checkArray) && count($checkArray) > 0) {
            } else {
                $checkArray = array('thopecive', 'pixelblogger', 'siliconmarket', 'technoraven');
            }
            $pos = $this->strpos_arr($url, $checkArray);
            if ($pos === false) {
                //echo 'Word is not found in sentence';
                if (strpos($url, '?') !== false) {
                    $url .= $qryString;
                } else {
                    $url .= '?' . $qryString;
                }
                $url = str_ireplace('##visitor_id##', $visitorId, $url);
                return $url;
            } else {

                if (strpos($url, '?') !== false) {
                    $url .= $qryString_special;
                } else {
                    $url .= '?' . $qryString_special;
                }
                return $url;
            }
        } else {
            return '';
        }
    }
    /**
     * Strpos arr
     *
     * @param $haystack
     * @param $needle
     * @return false|int
     */
    public static function strpos_arr($haystack, $needle)
    {
        if (!is_array($needle)) $needle = array($needle);
        foreach ($needle as $what) {
            if (($pos = strpos($haystack, $what)) !== false) return $pos;
        }
        return false;
    }
    /**
     * Get redirection URL
     *
     * @param $arrUrlParams
     * @param string $full_url
     * @param string $domain
     * @return array|string|string[]
     */
    public static function getRedirectionURL($arrUrlParams, $full_url = "", $domain = "")
    {
        extract($arrUrlParams);
        $findParam['tracker_type'] = $tracker_type;
        $findParam['device'] = $strSiteFlag;
        $findParam['vendor'] = $vendor;
        $findParam['source'] = $source;
        $findParam['vertical'] = $vertical;
        $findParam['browser'] = $browser;
        $findParam['platform'] = $platform;
        $completedUrl = '';
        $redirect_url = '';

        if (isset($tracker) && ($tracker == 'ADTOPIA' || $tracker == 'ADTOPIA2')) {
            if (!empty($acacnt) && in_array($acacnt, $yahoo_accnt)) {
                /*-----------------Yahoo-------------- */
                if ($tracker == 'ADTOPIA') {
                    $redirect_url = 'http://adtopia.club/native/adv_track.php';

                } else {
                    $redirect_url = 'http://track.adtopia.club/native_adv_track.php';
                }

            } else if (isset($lp_id) && !empty($lp_id) && ($tracker == 'ADTOPIA2' || $pixel > 2500000000)) {
                /*------------From adtopia using lpId----------- */
                $DynamicUrl = new DynamicUrlRepository();
                $redirect_url = $DynamicUrl->getLPLink($lp_id);
            } else if (isset($url_id) && !empty($url_id) && $tracker == 'ADTOPIA' && $pixel < 2500000000) {

                $arrInfo = GeneralClass::fnFileGetContent('http://adtopia.club/native/lp_probe_live_tracking.php?url_id=' . $url_id);

                if ($arrInfo['result'] == 'Success') {
                    $info = $arrInfo['result_detail'];
                    $myArray = json_decode($info, true);

                    if ($myArray['status'] == 1) {
                        $redirect_url = 'http://adtopia.club/native/lp_track_prb_new.php';
                    }
                }
            }
        }
        if (empty($redirect_url)) {
            $DynamicUrl = new DynamicUrlRepository();
            $redirect_url = $DynamicUrl->getCustomURLFromDB($findParam, $full_url, $domain);
        }
        $urlgenerateParam = array(
            'visitorId' => $visitorId,
            'qryString' => $qryString,
            'checkArray' => $checkArray,
            'qryString_special' => $qryString_special,
            'url' => $redirect_url
        );
        $DynamicUrl = new DynamicUrlRepository();
        $completedUrl = $DynamicUrl->generateRedirectlink($urlgenerateParam);
        return $completedUrl;
    }
    /**
     * Get LP Link
     *
     * @param $linkId
     * @return string
     */
    public function getLPLink($linkId)
    {
        $url = '';
        $retres = DB::connection('mysql_atp')->table('atp_assets')
            ->select('page_url')
            ->where('id', $linkId)
            ->get();

        if ($retres->count() > 0) {

            $url = $retres[0]->page_url;
        }
        return $url;
    }
    /**
     * Get custom URL from DB
     *
     * @param $findParam
     * @param string $full_url
     * @param string $domain
     * @return string
     */
    public function getCustomURLFromDB($findParam, $full_url = "", $domain = "")
    {
        extract($findParam);
        $adv_page = isset($adv_page) ? $adv_page : '';
        $urlArray = $urlArray1 = array();
        $defaultLik = $redirect_url = '';
        // $domain 	 = trim( env( 'APP_URL' ), '/' );
        if (strpos($full_url, '?') !== false) {
            $url = substr($full_url, 0, strpos($full_url, '?'));
        } else {
            $url = $full_url;
        }
        $currentPage = $url;
        $domain = str_ireplace('http://', '', $domain);
        $domain = str_ireplace('https://', '', $domain);
        $currentPage = str_ireplace('http://', '', $currentPage);
        $currentPage = str_ireplace('https://', '', $currentPage);
        $query = DB::connection('mysql_atp')->table('adv_redirection AS ar')
            ->leftJoin('adv_redirection_case AS arc', 'ar.id', '=', 'arc.adv_redirection_id')
            ->leftJoin('adv_redirection_case_urls as arcr', 'arc.id', '=', 'arcr.adv_redirection_case_id')
            ->select('ar.domain', 'arc.file_name', 'arc.tracker', 'arc.vertical', 'arc.device', 'arc.source', 'arc.vendor', 'arc.browser', 'arc.platform', 'arc.device_model', 'arcr.redirect_link', 'arcr.percentage')
            ->where('ar.domain', $domain)
            ->orWhere('ar.domain', $currentPage)
            ->where('arc.status', '=', '1')
            ->where('arcr.percentage', '>', 1);

        if ($tracker_type) {
            $query->whereIn('arc.tracker', array('any', $tracker_type));
        }
        if ($device) {
            $query->whereIn('arc.device', array('any', $device));
        }
        if ($vendor) {
            $query->whereIn('arc.vendor', array('any', $vendor));
        }
        if ($source) {
            $query->whereIn('arc.source', array('any', $source));
        }
        if ($vertical) {
            $query->whereIn('arc.vertical', array('any', $vertical));
        }
        if ($browser) {
            $query->whereIn('arc.browser', array('any', $browser));
        }
        if ($platform) {
            $query->whereIn('arc.platform', array('any', $platform));
        }
        if ($adv_page) {
            $query->whereIn('arc.file_name', array($adv_page));
        }
        $resulturls = $query->get();
        if ($resulturls->count() > 0) {
            $i = 0;
            foreach ($resulturls as $key => $row) {

                $urlArray[$i] = json_decode(json_encode($row), True);
                $urlArray[$i]['weight'] = 0;

                if ($row->domain == $currentPage) {
                    $urlArray[$i]['weight'] += 6;
                } else if ($row->domain == $domain) {
                    $urlArray[$i]['weight'] += 3;
                }

                if ($row->tracker == '' || $row->tracker == 'any') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->tracker == $tracker_type) {
                    $urlArray[$i]['weight'] += 18;
                }

                if ($row->vertical == 'any') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->vertical == $vertical) {
                    $urlArray[$i]['weight'] += 27;
                }

                if ($row->device == 'any') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->device == $device) {
                    $urlArray[$i]['weight'] += 36;
                }

                if ($row->source == 'any') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->source == $source) {
                    $urlArray[$i]['weight'] += 45;
                }

                if ($row->vendor == 'any') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->vendor == $vendor) {
                    $urlArray[$i]['weight'] += 54;
                }

                if ($row->browser == 'any') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->browser == $browser) {
                    $urlArray[$i]['weight'] += 63;
                }

                if ($row->platform == 'any') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->platform == $platform) {
                    $urlArray[$i]['weight'] += 72;
                }

                if ($row->file_name == '') {
                    $urlArray[$i]['weight'] += 1;
                } else if ($row->file_name == $adv_page) {
                    $urlArray[$i]['weight'] += 321;
                }

                $i++;
            }
        }
        if (count($urlArray) > 0) {
            $final = array_column($urlArray, 'weight', 'weight');
            $final_key = max(array_keys($final));
            $max_val = $final[$final_key];

            foreach ($urlArray as $eachrow) {
                if (in_array($max_val, $eachrow)) {
                    if ($eachrow['percentage'] == 'any' || $eachrow['percentage'] == '' || $eachrow['percentage'] == 'NULL') {
                        $urlArray1[] = array('url' => $eachrow['redirect_link'], 'percentage' => 100);
                    } else {
                        $urlArray1[] = array('url' => $eachrow['redirect_link'], 'percentage' => $eachrow['percentage']);
                    }
                }
            }

            do {
                $index_key = (mt_rand(1, 100));
                $numerical_array[$index_key] = '';
            } while (count($numerical_array) < 100);
            $DynamicUrl = new DynamicUrlRepository();
            $redirect_url = $DynamicUrl->getRandomURL($urlArray1, $numerical_array);
        }
        return $redirect_url;
    }
    /**
     * Get Random URL
     *
     * @param $array
     * @param $numerical_array
     * @return mixed
     */
    function getRandomURL($array, $numerical_array)
    {
        $winner = (mt_rand(1, 100));
        $inital_value = 0;
        $final_value = 0;
        foreach ($array as $key => $value) {
            $final_value = $final_value + $value['percentage'];
            $output = array_slice($numerical_array, $inital_value, $final_value, true);
            foreach ($output as $key1 => $value1) {
                $numerical_array[$key1] = $value['url'];
            }
            $inital_value = $final_value;
        }
        return $numerical_array[$winner];
    }
}
