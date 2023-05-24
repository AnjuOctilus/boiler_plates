<?php

namespace App\Repositories;

use App\Models\CarLender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\SplitInfo;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\DomainDetail;
use App\Models\Bank;
use Carbon\Carbon;
use App\Repositories\UARepository;
use App\Repositories\VisitorRepository;
use App\Repositories\PixelFireRepository;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\Interfaces\CommonSplitsInterface;
use App\Models\CarDealer;

/**
 * Class CommonSplitsRepository
 *
 * @package App\Repositories
 */
class CommonSplitsRepository implements CommonSplitsInterface
{
    /**
     * CommonSplitsRepository constructor.
     */
    public function __construct()
    {
        $this->uaRepo = new UARepository;
        $this->visitorRepo = new VisitorRepository;
        $this->pixelFireRepo = new PixelFireRepository;
        $this->commonFunctionRepo = new CommonFunctionsRepository;
    }

    /**
     * Init split
     *
     * @param $request
     * @param $splitName
     * @return array
     */
    public function initSplit($request, $splitName)
    {
        $this->splitName = $splitName;
        $arrUserAgentInfo = $this->uaRepo->parse_user_agent();
        //Array to save visitor id and split info id
        $return_arr = array();
        // Identify the user country
        $splitPath = $request->root() . '/' . $request->path();
        $countryCode = $arrUserAgentInfo['country'];
        $strSiteFlag = $arrUserAgentInfo['device'];
        $intSiteFlagId = $arrUserAgentInfo['siteFlagId'];
        $strBrowser = $arrUserAgentInfo['browser'];
        $strPlatform = $arrUserAgentInfo['platform'];
        //insertion to split_info table and domain details table
        $return_arr['split_info_id'] = self::dynamicSplitAdd($this->splitName, $splitPath);
        $currentUrl = URL::full();
        $intAffiliateId = 0;
        $strScrResolution = '';
        $strErrorMessage = '';
        $token_decoded = '';
        $ext_var2 = '';
        $strTransid = ($request->has('transid')) ? $request->transid : '';
        $strCampaign = ($request->has('campaign')) ? $request->campaign : '';
        $strOfferId = ($request->has('aff_id')) ? $request->aff_id : '';
        $intYlbAffId = ($request->has('test')) ? $request->test : '';
        $intYlbAffSub = ($request->has('aff_sub')) ? $request->aff_sub : '';
        $aff_sub2 = ($request->has('aff_sub2')) ? $request->aff_sub2 : '';
        $aff_sub3 = ($request->has('aff_sub3')) ? $request->aff_sub3 : '';
        $aff_sub4 = ($request->has('aff_sub4')) ? $request->aff_sub4 : '';
        $aff_sub5 = ($request->has('aff_sub5')) ? $request->aff_sub5 : '';
        $source = ($request->has('source')) ? $request->source : '';
        $tid = ($request->has('tid')) ? $request->tid : '';
        $pid = ($request->has('pid')) ? $request->pid : '';
        $thr_source = ($request->has('thr_source')) ? $request->thr_source : '';
        $thr_transid = ($request->has('thr_transid')) ? $request->thr_transid : '';
        $thr_sub1 = ($request->has('thr_sub1')) ? $request->thr_sub1 : '';
        $thr_sub2 = ($request->has('thr_sub2')) ? $request->thr_sub2 : '';
        $thr_sub3 = ($request->has('thr_sub3')) ? $request->thr_sub3 : '';
        $thr_sub4 = ($request->has('thr_sub4')) ? $request->thr_sub4 : '';
        $thr_sub5 = ($request->has('thr_sub5')) ? $request->thr_sub5 : '';
        $thr_sub6 = ($request->has('thr_sub6')) ? $request->thr_sub6 : '';
        $thr_sub7 = ($request->has('thr_sub7')) ? $request->thr_sub7 : '';
        $thr_sub8 = ($request->has('thr_sub8')) ? $request->thr_sub8 : '';
        $thr_sub9 = ($request->has('thr_sub9')) ? $request->thr_sub9 : '';
        $thr_sub10 = ($request->has('thr_sub10')) ? $request->thr_sub10 : '';
        $atp_source = ($request->has('atp_source')) ? $request->atp_source : '';
        $atp_vendor = ($request->has('atp_vendor')) ? $request->atp_vendor : '';
        $atp_sub1 = ($request->has('atp_sub1')) ? $request->atp_sub1 : '';
        $atp_sub2 = ($request->has('atp_sub2')) ? $request->atp_sub2 : '';
        $atp_sub3 = ($request->has('atp_sub3')) ? $request->atp_sub3 : '';
        $atp_sub4 = ($request->has('atp_sub4')) ? $request->atp_sub4 : '';
        $atp_sub5 = ($request->has('atp_sub5')) ? $request->atp_sub5 : '';
        ##Media vendor parameter
        $media_vendor = ($request->has('media_vendor')) ? $request->media_vendor : '';
        ###  Extra details parameter ##
        $ext_var1 = ($request->has('ext_var1')) ? $request->ext_var1 : '';
        //vendorclick id adtopia
        $ext_var2 = ($request->has('ext_var2')) ? $request->ext_var2 : '';
        $ext_var3 = ($request->has('ext_var3')) ? $request->ext_var3 : '';
        $ext_var4 = ($request->has('ext_var4')) ? $request->ext_var4 : '';
        $ext_var5 = ($request->has('ext_var5')) ? $request->ext_var5 : '';
        ###  Extra details parameter ##
        $tracker = ($request->has('tracker')) ? $request->tracker : '';
        $pixel = ($request->has('pixel')) ? $request->pixel : '';
        $redirectDomain = ($request->has('domain')) ? $request->domain : '';
        $adv_vis_id = ($request->has('adv_vis_id')) ? $request->adv_vis_id : '';
        $adv_page = ($request->has('adv_page')) ? $request->adv_page : '';
        ## YLB tracking campaign
        if ($strCampaign != '') {
            $intAffiliateId = $this->commonFunctionRepo->getCampaignAffID($strCampaign, $intSiteFlagId, $strOfferId);
        }
        ## LP Duplication Checking
        $ext_var2 = $token_decoded = '';
        if (!$request->has('ext_var2') && $request->has('token')) {
            $atp_token = $request->has('token') ? $request->token : '';
            $token_decoded = $this->commonFunctionRepo->stringcrypt($atp_token, 'd');
            $current_time = Carbon::now();
            $from_time = strtotime($token_decoded);
            $to_time = strtotime($current_time);
            $time_diff = round(abs($to_time - $from_time));
            //. ' seconds';
            if ($time_diff > 300) {
                $ext_var2 = '1';
            } else {
                $ext_var2 = '0';
            }
        } else if ($request->has('ext_var2')) {
            $ext_var2 = $request->ext_var2;
        } else if ((strtoupper($tracker) == 'ADTOPIA' || strtoupper($tracker) == 'ADTOPIA2') && !$request->has('token')) {
            $ext_var2 = '1';
        }
        //Define array parameters for visitor Id creation
        $arrParam = array(
            'file_name' => $this->splitName,
            'split_path' => $splitPath,
            'affiliate_id' => $intAffiliateId,
            'transid' => $strTransid,
            'site_flag_id' => $strSiteFlag,
            'scr_resolution' => $strScrResolution,
            'country' => $countryCode,
            'browser' => $strBrowser,
            'platform' => $strPlatform,
            'site_flag' => $intSiteFlagId,
            'aff_id' => $intYlbAffId,
            'aff_sub' => $intYlbAffSub,
            'offer_id' => $strOfferId,
            'aff_sub2' => $aff_sub2,
            'aff_sub3' => $aff_sub3,
            'aff_sub4' => $aff_sub4,
            'aff_sub5' => $aff_sub5,
            'campaign' => $strCampaign,
            'source' => $source,
            'tid' => $tid,
            'pid' => $pid,
            'thr_source' => $thr_source,
            'thr_transid' => $thr_transid,
            'thr_sub1' => $thr_sub1,
            'thr_sub2' => $thr_sub2,
            'thr_sub3' => $thr_sub3,
            'thr_sub4' => $thr_sub4,
            'thr_sub5' => $thr_sub5,
            'thr_sub6' => $thr_sub6,
            'thr_sub7' => $thr_sub7,
            'thr_sub8' => $thr_sub8,
            'thr_sub9' => $thr_sub9,
            'thr_sub10' => $thr_sub10,
            'pixel' => $pixel,
            'tracker' => $tracker,
            'atp_source' => $atp_source,
            'atp_vendor' => $atp_vendor,
            'atp_sub1' => $atp_sub1,
            'atp_sub2' => $atp_sub2,
            'atp_sub3' => $atp_sub3,
            'atp_sub4' => $atp_sub4,
            'atp_sub5' => $atp_sub5,
            'media_vendor' => $media_vendor,
            'ext_var1' => $ext_var1,
            'ext_var2' => $ext_var2,
            'ext_var3' => $ext_var3,
            'ext_var4' => $ext_var4,
            'ext_var5' => $ext_var5,
            'adv_vis_id' => $adv_vis_id,
            'adv_page' => $adv_page,
            'redirectDomain' => $redirectDomain,
        );
        $visitors = $this->visitorRepo->saveVisitor($arrParam);
        $return_arr['intVisitorId'] = $intVisitorId = $visitors['visitor_id'];
        $tracker_type = $visitors['tracker_type'];
        $flagLPVisit = $this->pixelFireRepo->getPixelFireStatus('LP', $intVisitorId);

        $atplog = '0';
        $adtopiapixel = '';

        $response = '';
        $strResult = '';
        if (!$flagLPVisit) {
            if ($tracker_type == 1) {
                $chkArry = array(
                    'tracker_type' => $tracker_type,
                    'tracker' => $tracker,
                    'atp_vendor' => $atp_vendor,
                    'pixel' => $pixel,
                    'pixel_type' => 'LP',
                    'statusupdate' => 'SPLIT',
                    'intVisitorId' => $intVisitorId,
                    'redirecturl' => $currentUrl
                );
                $arrResultDetail = $this->pixelFireRepo->atpPixelFire($chkArry);
                if ($arrResultDetail) {
                    $strResult = $arrResultDetail['result'];
                    $response = $arrResultDetail['result_detail'];
                    $adtopiapixel = $arrResultDetail['adtopiapixel'];
                }
            }
            $this->pixelFireRepo->setPixelFireStatus('LP', $intVisitorId);
        }
        return $return_arr;
    }

    /**
     * Dynamic split add
     *
     * @param $strFileName
     * @param $splitPath
     * @return mixed
     */
    public function dynamicSplitAdd($strFileName, $splitPath)
    {
        $strSplitName = strtolower($strFileName);
        $splitInfo = SplitInfo::where('split_name', 'LIKE', $strSplitName)->first();
        if (!$splitInfo) {
            $domain_name = env('APP_URL');
            $domain_result = DomainDetail::select('id', 'type')
                ->where('domain_name', '=', $domain_name)
                ->first();
            if (!empty($domain_result)) {
                $domain_id = $domain_result->id;
                $domain_type = $domain_result->type;
                if ($domain_result->type == 'Adv') {
                    $domain_result->update(array('type' => 'Both'));
                }
            } else {
                $domainDetail = new DomainDetail;
                $domainDetail->domain_name = $domain_name;
                $domainDetail->type = 'LP';
                $domainDetail->last_active_date = Carbon::now();
                $domainDetail->save();
                $domain_id = $domainDetail->id;
            }
            $objSplitInfo = new SplitInfo;
            $objSplitInfo->domain_id = $domain_id;
            $objSplitInfo->split_name = $strFileName;
            $objSplitInfo->split_path = $splitPath;
            $objSplitInfo->last_active_date = Carbon::now();
            $objSplitInfo->save();

            return $objSplitInfo->id;

        } else {
            return $splitInfo->id;
        }
    }

    /**
     * Dynamic split add new
     *
     * @param $strFileName
     * @param $splitPath
     * @param $domainName
     * @return mixed
     */
    public function dynamicSplitAddNew($strFileName, $splitPath, $domainName)
    {
        $strSplitName = strtolower($strFileName);
        $splitInfo = SplitInfo::where('split_name', 'LIKE', $strSplitName)->first();
        if (!$splitInfo) {
            $domain_name = $domainName;
            $domain_result = DomainDetail::select('id', 'type')
                ->where('domain_name', '=', $domain_name)
                ->first();
            if (!empty($domain_result)) {
                $domain_id = $domain_result->id;
                $domain_type = $domain_result->type;
                if ($domain_result->type == 'Adv') {
                    $domain_result->update(array('type' => 'Both'));
                }
            } else {
                $domainDetail = new DomainDetail;
                $domainDetail->domain_name = $domain_name;
                $domainDetail->type = 'LP';
                $domainDetail->last_active_date = Carbon::now();
                $domainDetail->save();
                $domain_id = $domainDetail->id;
            }
            $objSplitInfo = new SplitInfo;
            $objSplitInfo->domain_id = $domain_id;
            $objSplitInfo->split_name = $strFileName;
            $objSplitInfo->split_path = $splitPath;
            $objSplitInfo->last_active_date = Carbon::now();
            $objSplitInfo->save();

            return $objSplitInfo->id;

        } else {
            return $splitInfo->id;
        }
    }

    /**
     * Get split id from name
     *
     * @param $strSplitName
     * @param $intSiteFlagId
     * @return int
     */
    public function getSplitIdFromName($strSplitName, $intSiteFlagId)
    {
        $intSplitId = 0;
        $splitInfo = SplitInfo::select('id')
            ->where('split_name', '=', $strSplitName)
            ->first();
        if (!empty($splitInfo)) {
            $intSplitId = $splitInfo->id;
        }
        return $intSplitId;
    }

    /**
     * Get banks
     *
     * @param $type
     * @return mixed
     */
    public function getBanks($type)
    {
        $banks = Bank::select('id', 'bank_name', 'rank')
            ->where('status', '1')
            ->where('type', $type)
            ->orderBy('rank', 'ASC')
            ->get();
        return $banks;
    }

    /**
     * Get dealers
     *
     * @param $dealerName
     * @return mixed
     */
    public function getDealers($dealerName)
    {
        $dealers = CarDealer::select('id', 'dealer')
            ->where('dealer', 'LIKE', "%{$dealerName}%")
            ->get();
        return $dealers;
    }

    /**
     * Get Lenders
     *
     * @param $keyWord
     * @return mixed
     */
    public function getLenders($keyWord)
    {
        $lenders = CarLender::select('id', 'lender_name')
            ->where('lender_name', 'LIKE', "%{$keyWord}%")
            ->get();
        return $lenders;
    }
}
