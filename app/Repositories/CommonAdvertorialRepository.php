<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\Affliate;
use Carbon\Carbon;
use App\Repositories\DynamicUrlRepository;
use App\Repositories\VisitorRepository;
use App\Repositories\PixelFireRepository;
use App\Repositories\UARepository;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\Interfaces\CommonAdvertorialInterface;
use App\Repositories\AdvVisitorRepository;

/**
 * Class CommonAdvertorialRepository
 *
 * @package App\Repositories
 */
class CommonAdvertorialRepository implements CommonAdvertorialInterface
{
    /**
     * CommonAdvertorialRepository constructor.
     *
     * @param $splitName
     */
    public function __construct($splitName)
    {
        $this->splitName = $splitName;
    }

    /**
     * Click varibles
     *
     * @param $request
     * @return array
     */
    

    /**
     * Init advertorial
     *
     * @param $request
     * @return array
     */
    public function initAdvertorial($request)
    {
        $retrunArray = array();
        $ua = new UARepository();
        $arrUserAgentInfo = $ua->parse_user_agent();
        // Identify the user country
        $splitPath = $request->root() . "/" . $request->path();
        $countryCode = $arrUserAgentInfo['country'];
        $strSiteFlag = $arrUserAgentInfo['device'];
        $intSiteFlagId = $arrUserAgentInfo['siteFlagId'];
        $strBrowser = $arrUserAgentInfo['browser'];
        $strPlatform = $arrUserAgentInfo['platform'];
        $currentUrl = URL::full();
        $intAffiliateId = 0;
        $strScrResolution = "";
        $strErrorMessage = "";
        $token_decoded = "";
        $ext_var2 = "";
        $intVisitorId = 0;
        if (!$_POST) {
            $strTransid = ($request->has('transid')) ? $request->transid : "";
            $strCampaign = ($request->has('campaign')) ? $request->campaign : "";
            $strOfferId = ($request->has('aff_id')) ? $request->aff_id : "";
            $intYlbAffId = ($request->has('test')) ? $request->test : "";
            $intYlbAffSub = ($request->has('aff_sub')) ? $request->aff_sub : "";
            $aff_sub2 = ($request->has('aff_sub2')) ? $request->aff_sub2 : "";
            $aff_sub3 = ($request->has('aff_sub3')) ? $request->aff_sub3 : "";
            $aff_sub4 = ($request->has('aff_sub4')) ? $request->aff_sub4 : "";
            $aff_sub5 = ($request->has('aff_sub5')) ? $request->aff_sub5 : "";

            $source = ($request->has('source')) ? $request->source : "";
            $tid = ($request->has('tid')) ? $request->tid : "";
            $pid = ($request->has('pid')) ? $request->pid : "";

            $thr_source = ($request->has('thr_source')) ? $request->thr_source : "";
            $thr_transid = ($request->has('thr_transid')) ? $request->thr_transid : "";
            $thr_sub1 = ($request->has('thr_sub1')) ? $request->thr_sub1 : "";
            $thr_sub2 = ($request->has('thr_sub2')) ? $request->thr_sub2 : "";
            $thr_sub3 = ($request->has('thr_sub3')) ? $request->thr_sub3 : "";
            $thr_sub4 = ($request->has('thr_sub4')) ? $request->thr_sub4 : "";
            $thr_sub5 = ($request->has('thr_sub5')) ? $request->thr_sub5 : "";
            $thr_sub6 = ($request->has('thr_sub6')) ? $request->thr_sub6 : "";
            $thr_sub7 = ($request->has('thr_sub7')) ? $request->thr_sub7 : "";
            $thr_sub8 = ($request->has('thr_sub8')) ? $request->thr_sub8 : "";
            $thr_sub9 = ($request->has('thr_sub9')) ? $request->thr_sub9 : "";
            $thr_sub10 = ($request->has('thr_sub10')) ? $request->thr_sub10 : "";

            $atp_source = ($request->has('atp_source')) ? $request->atp_source : "";
            $atp_vendor = ($request->has('atp_vendor')) ? $request->atp_vendor : "";
            $atp_sub1 = ($request->has('atp_sub1')) ? $request->atp_sub1 : "";
            $atp_sub2 = ($request->has('atp_sub2')) ? $request->atp_sub2 : "";
            $atp_sub3 = ($request->has('atp_sub3')) ? $request->atp_sub3 : "";
            $atp_sub4 = ($request->has('atp_sub4')) ? $request->atp_sub4 : "";
            $atp_sub5 = ($request->has('atp_sub5')) ? $request->atp_sub5 : "";
            ###  Extra details parameter ##
            $ext_var1 = ($request->has('ext_var1')) ? $request->ext_var1 : "";//vendorclick id adtopia
            $ext_var2 = ($request->has('ext_var2')) ? $request->ext_var2 : "";
            $ext_var3 = ($request->has('ext_var3')) ? $request->ext_var3 : "";
            $ext_var4 = ($request->has('ext_var4')) ? $request->ext_var4 : "";
            $ext_var5 = ($request->has('ext_var5')) ? $request->ext_var5 : "";
            ###  Extra details parameter ##
            $tracker = ($request->has('tracker')) ? $request->tracker : "";
            $pixel = ($request->has('pixel')) ? $request->pixel : "";

            $acid = ($request->has('acid')) ? $request->acid : "";
            $cid = ($request->has('cid')) ? $request->cid : "";
            $crvid = ($request->has('crvid')) ? $request->crvid : "";
            $acacnt = ($request->has('acacnt')) ? $request->acacnt : "";
            $acsrc = ($request->has('acsrc')) ? $request->acsrc : "";

            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $strDomainName = $_SERVER['SERVER_NAME'];
            $strRefererSite = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $cf = new CommonFunctionsRepository();

            ## YLB tracking campaign
            if ($strCampaign != '') {
                $intAffiliateId = $cf->getCampaignAffID($strCampaign, $intSiteFlagId, $strOfferId);
            }
            ## LP Duplication Checking
            $ext_var2 = $token_decoded = '';
            if (!$request->has('ext_var2') && $request->has('token')) {
                $atp_token = $request->has('token') ? $request->token : "";
                $token_decoded = $cf->stringcrypt($atp_token, 'd');
                $current_time = Carbon::now();
                $from_time = strtotime($token_decoded);
                $to_time = strtotime($current_time);
                $time_diff = round(abs($to_time - $from_time));//. " seconds";
                if ($time_diff > 300) {
                    $ext_var2 = '1';
                } else {
                    $ext_var2 = '0';
                }
            } else if ($request->has('ext_var2')) {
                $ext_var2 = $request->ext_var2;
            } else if ((strtoupper($tracker) == "ADTOPIA" || strtoupper($tracker) == "ADTOPIA2") && !$request->has('token')) {
                $ext_var2 = '1';
            }
            //Define array parameters for visitor Id creation
            $arrParam = array(
                "file_name" => $this->splitName,
                "split_path" => $splitPath,
                "affiliate_id" => $intAffiliateId,
                "transid" => $strTransid,
                'site_flag_id' => $strSiteFlag,
                "scr_resolution" => $strScrResolution,
                "country" => $countryCode,
                "browser" => $strBrowser,
                "platform" => $strPlatform,
                "site_flag" => $intSiteFlagId,
                "aff_id" => $intYlbAffId,
                "aff_sub" => $intYlbAffSub,
                "offer_id" => $strOfferId,
                "aff_sub2" => $aff_sub2,
                "aff_sub3" => $aff_sub3,
                "aff_sub4" => $aff_sub4,
                "aff_sub5" => $aff_sub5,
                "campaign" => $strCampaign,
                "source" => $source,
                "tid" => $tid,
                "thr_source" => $thr_source,
                "thr_transid" => $thr_transid,
                "thr_sub1" => $thr_sub1,
                "thr_sub2" => $thr_sub2,
                "thr_sub3" => $thr_sub3,
                "thr_sub4" => $thr_sub4,
                "thr_sub5" => $thr_sub5,
                "thr_sub6" => $thr_sub6,
                "thr_sub7" => $thr_sub7,
                "thr_sub8" => $thr_sub8,
                "thr_sub9" => $thr_sub9,
                "thr_sub10" => $thr_sub10,
                "pixel" => $pixel,
                "tracker" => $tracker,
                "atp_source" => $atp_source,
                "atp_vendor" => $atp_vendor,
                "atp_sub1" => $atp_sub1,
                "atp_sub2" => $atp_sub2,
                "atp_sub3" => $atp_sub3,
                "atp_sub4" => $atp_sub4,
                "atp_sub5" => $atp_sub5,
                "ext_var1" => $ext_var1,
                "ext_var2" => $ext_var2,
                "ext_var3" => $ext_var3,
                "ext_var4" => $ext_var4,
                "ext_var5" => $ext_var5,
                "acid" => $acid,
                "cid" => $cid,
                "crvid" => $crvid,
                "pid" => $pid,
                "referer_site" => $strRefererSite,
                "existingdomain" => $currentUrl
            );
            $advVisitor = new AdvVisitorRepository();
            $advvisitors = $advVisitor->saveADVVisitor($arrParam);
            $cf = new CommonFunctionsRepository();
            $pixelFire = new PixelFireRepository();
            $intAdvVisitorId = $advvisitors['adv_visitor_id'];
            $tracker_type = $advvisitors['tracker_type'];
            $flagAPVisit = $pixelFire->getAdvPixelFireStatus("AP", $intAdvVisitorId);
            $retrunArray['flagAPVisit'] = $flagAPVisit;
            $retrunArray['intAdvVisitorId'] = $intAdvVisitorId;
            $retrunArray['tracker_type'] = $cf->getTrackerType($tracker_type);
            $atplog = "0";
            $adtopiapixel = "";
            $response = "";
            $strResult = "";
            if (!$flagAPVisit) {
                if ($tracker_type == 1) {
                    $chkArry = array(
                        "tracker_type" => $tracker_type,
                        "tracker" => $tracker,
                        "atp_vendor" => $atp_vendor,
                        "pixel" => $pixel,
                        "pixel_type" => "AP",
                        "statusupdate" => "ADV",
                        "intVisitorId" => $intAdvVisitorId,
                        "redirecturl" => $currentUrl
                    );
                    $arrResultDetail = $pixelFire->atpPixelFire($chkArry);
                    if ($arrResultDetail) {
                        $strResult = $arrResultDetail['result'];
                        $response = $arrResultDetail['result_detail'];
                        $adtopiapixel = $arrResultDetail['adtopiapixel'];
                    }
                }
                $pixelFire->setAdvPixelFireStatus("AP", $intAdvVisitorId);
            }
        } 

        return $retrunArray;
    }

    /**
     * Redirect url
     *
     * @param $request
     * @param $inADVvisitorId
     * @param $strFileName
     * @param $visitorParams
     * @param string $full_url
     * @param string $domain
     * @return array|string|string[]
     */
    public function redirectUrl($request, $inADVvisitorId, $strFileName, $visitorParams, $full_url = "", $domain = "")
    {
        $redirect_url = '';
        $advVisitor = new AdvVisitorRepository();
        $splitPath = '';
        $countryCode = $visitorParams['country'];
        $strSiteFlag = $visitorParams['device'];
        $intSiteFlagId = $visitorParams['siteFlagId'];
        $strBrowser = $visitorParams['browser'];
        $strPlatform = $visitorParams['platform'];
        $findTracker = $advVisitor->findTracker($request);
        $tracker_type = $findTracker['tracker_type'];
        $tracker = $findTracker['tracker'];
        $aff_id = $findTracker['aff_id'];
        $atp_vendor = $findTracker['atp_vendor'];
        $atp_source = $findTracker['atp_source'];
        $baseTracker = $findTracker['baseTracker'];

        $pixel = (isset($request->pixel)) ? $request->pixel : "";
        $atp_sub1 = (isset($request->atp_sub1)) ? $request->atp_sub1 : "";
        $atp_sub2 = (isset($request->atp_sub2)) ? $request->atp_sub2 : "";
        $atp_sub3 = (isset($request->atp_sub3)) ? $request->atp_sub3 : "";
        $atp_sub4 = (isset($request->atp_sub4)) ? $request->atp_sub4 : "";
        $atp_sub5 = (isset($request->atp_sub5)) ? $request->atp_sub5 : "";
        $thr_source = (isset($request->thr_source)) ? $request->thr_source : "";

        $arrUrlParams = array();
        $arrUrlParams = $findTracker;
        $arrUrlParams['url_id'] = (isset($request->url_id)) ? $request->url_id : "";
        $arrUrlParams['lp_id'] = (isset($request->lp_id)) ? $request->lp_id : "";
        $arrUrlParams['strSiteFlag'] = $strSiteFlag;
        $arrUrlParams['acacnt'] = (isset($request->acacnt)) ? $request->acacnt : "";
        $arrUrlParams['pixel'] = $pixel;
        $arrUrlParams['other_urls'] = array();
        $arrUrlParams['yahoo_accnt'] = array('YMS', 'YMIL', 'YMIF');

        //Create UUID
        $uuid = $visitorParams['uuid'];
        $urlprms = 'adv_vis_id=' . $inADVvisitorId . '&adv_page=' . $strFileName . '&adv_page_name=' . $strFileName . '&uuid=' . $uuid;
        foreach ($request as $key => $val) {
            $urlprms .= "&";
            $urlprms .= $key . "=" . $val;
        }
        $arrUrlParams['qryString'] = $urlprms;
        $arrUrlParams['checkArray'] = array('thopecive', 'pixelblogger', 'siliconmarket', 'technoraven');
        $arrUrlParams['qryString_special'] = "&s1=" . $atp_sub1 . "&s2=" . $pixel . "&s3=" . $atp_sub2 . "&s4=" . $atp_sub3 . "&s5=" . $strFileName;
        $arrUrlParams['visitorId'] = $inADVvisitorId;


        $action_type = !isset($action_type) ? "AddClick" : "";
        if (!isset($page) || empty($page)) {
            switch ($strFileName) {
                default :
                    $page = 'lifeinsurance';
                    break;
            }
        }
        /*new params*/
        $arrUrlParams['vendor'] = '';
        $arrUrlParams['source'] = '';
        $arrUrlParams['vertical'] = $page;
        $arrUrlParams['browser'] = $strBrowser;
        $arrUrlParams['platform'] = $strPlatform;

        if ($atp_source) {
            $arrUrlParams['source'] = $atp_source;
        } else if ($thr_source) {
            $arrUrlParams['source'] = $thr_source;
        }

        if ($atp_vendor) {
            $arrUrlParams['vendor'] = $atp_vendor;
        }
        if (!isset($redirect_url) || empty($redirect_url)) {
            $dynamicUrl = new  DynamicUrlRepository();
            $redirect_url = $dynamicUrl->getRedirectionURL($arrUrlParams, $full_url, $domain);
        }

        return $redirect_url;

    }

}
