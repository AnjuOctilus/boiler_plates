<?php

namespace App\Repositories;

use App\Repositories\CommonFunctionsRepository;
use App\Models\SiteFlagMaster;
use App\Models\AdvVisitor;
use App\Models\AdvVisitorsCount;
use App\Models\AdvVisitorsLastVisit;
use App\Models\AdvAdtopiaDetail;
use App\Models\AdvExtraDetail;
use App\Models\AdvPixelFiring;
use App\Models\AdvThriveDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use App\Repositories\LogRepository;
use DB;
use App\Repositories\DynamicUrlRepository;
use App\Repositories\AdvVisitorRepository;
use App\Repositories\VisitorRepository;
use App\Repositories\Interfaces\AdvVisitorInterface;

/**
 * Class AdvVisitorRepository
 *
 * @package App\Repositories
 */
class AdvVisitorRepository implements AdvVisitorInterface
{
    /**
     * AdvVisitorRepository constructor.
     *
     *
     */
    public function __construct()
    {
        $this->duRepo = new DynamicUrlRepository;
        $this->commonFunctionRepo = new CommonFunctionsRepository;
        $this->visitorRepo = new VisitorRepository;
    }

    /**
     * Save ADV visitor
     *
     * @param $arrParam
     * @return array
     */
    public static function saveADVVisitor($arrParam)
    {

        $strFileName = $arrParam['file_name'];
        $splitPath = $arrParam['split_path'];
        $strAffiliate = $arrParam['affiliate_id'];
        $strTransid = $arrParam['transid'];
        $strScrResolution = $arrParam['scr_resolution'];
        $countryCode = $arrParam['country'];
        $device = $arrParam['site_flag_id'];
        $strBrowserType = $arrParam['browser'];
        $strDeviceType = $arrParam['platform'];
        $intSiteFlagId = $arrParam['site_flag'];
        $intAffId = $arrParam['aff_id'];
        $intAffSub = $arrParam['aff_sub'];
        $strOfferId = $arrParam['offer_id'];
        $aff_sub2 = $arrParam['aff_sub2'];
        $aff_sub3 = $arrParam['aff_sub3'];
        $aff_sub4 = $arrParam['aff_sub4'];
        $aff_sub5 = $arrParam['aff_sub5'];
        $strCampaign = ($arrParam['campaign'] != '') ? $arrParam['campaign'] : '';
        $source = ($arrParam['source'] != '') ? $arrParam['source'] : '';
        $tid = ($arrParam['tid'] != '') ? $arrParam['tid'] : '';
        $pid = ($arrParam['pid'] != '') ? $arrParam['pid'] : '';
        $thr_source = ($arrParam['thr_source'] != '') ? $arrParam['thr_source'] : '';
        $thr_transid = ($arrParam['thr_transid'] != '') ? $arrParam['thr_transid'] : '';
        $thr_sub1 = ($arrParam['thr_sub1'] != '') ? $arrParam['thr_sub1'] : '';
        $thr_sub2 = ($arrParam['thr_sub2'] != '') ? $arrParam['thr_sub2'] : '';
        $thr_sub3 = ($arrParam['thr_sub3'] != '') ? $arrParam['thr_sub3'] : '';
        $thr_sub4 = ($arrParam['thr_sub4'] != '') ? $arrParam['thr_sub4'] : '';
        $thr_sub5 = ($arrParam['thr_sub5'] != '') ? $arrParam['thr_sub5'] : '';
        $thr_sub6 = ($arrParam['thr_sub6'] != '') ? $arrParam['thr_sub6'] : '';
        $thr_sub7 = ($arrParam['thr_sub7'] != '') ? $arrParam['thr_sub7'] : '';
        $thr_sub8 = ($arrParam['thr_sub8'] != '') ? $arrParam['thr_sub8'] : '';
        $thr_sub9 = ($arrParam['thr_sub9'] != '') ? $arrParam['thr_sub9'] : '';
        $thr_sub10 = ($arrParam['thr_sub10'] != '') ? $arrParam['thr_sub10'] : '';
        $pixel = ($arrParam['pixel'] != '') ? $arrParam['pixel'] : '';
        $tracker = ($arrParam['tracker'] != '') ? $arrParam['tracker'] : '';
        $atp_source = ($arrParam['atp_source'] != '') ? $arrParam['atp_source'] : '';
        $atp_vendor = ($arrParam['atp_vendor'] != '') ? $arrParam['atp_vendor'] : '';
        $atp_sub1 = ($arrParam['atp_sub1'] != '') ? $arrParam['atp_sub1'] : '';
        $atp_sub2 = ($arrParam['atp_sub2'] != '') ? $arrParam['atp_sub2'] : '';
        $atp_sub3 = ($arrParam['atp_sub3'] != '') ? $arrParam['atp_sub3'] : '';
        $atp_sub4 = ($arrParam['atp_sub4'] != '') ? $arrParam['atp_sub4'] : '';
        $atp_sub5 = ($arrParam['atp_sub5'] != '') ? $arrParam['atp_sub5'] : '';

        ## extra parameter ##
        $ext_var1 = ($arrParam['ext_var1'] != '') ? $arrParam['ext_var1'] : '';
        $ext_var2 = ($arrParam['ext_var2'] != '') ? $arrParam['ext_var2'] : '';
        $ext_var3 = ($arrParam['ext_var3'] != '') ? $arrParam['ext_var3'] : '';
        $ext_var4 = ($arrParam['ext_var4'] != '') ? $arrParam['ext_var4'] : '';
        $ext_var5 = ($arrParam['ext_var5'] != '') ? $arrParam['ext_var5'] : '';
        $commonFunctionRepo = new CommonFunctionsRepository;
        $visitorRepo = new VisitorRepository;

        ## extra parameter ##
        $tracker_type = $visitorRepo->defineTrackerType($arrParam);
        //Redefine tracker if tracker value become empty
        if (empty($tracker)) {
            if ($tracker_type == 2) {
                $tracker = 'HO';
            } else if ($tracker_type == 3) {
                $tracker = 'THRIVE';
            } else if (substr($tracker_type, 0, 2) == 'FB') {
                $tracker = 'FB';
            } else if ($tracker_type == 5) {
                $tracker = 'GOOGLE';
            }
        }
        $commonFunctionRepo->dynamicAdvertorialsAdd($strFileName, $splitPath);
        if ($intAffId == '') {
            $intAffId = 0;
        }
        if ($intAffSub == '') {
            $intAffSub = 0;
        }
        if ($strOfferId == '') {
            $strOfferId = 0;
        }
        if (empty($strBrowserType)) {
            $strBrowserType = 'Unknown Browser';
        }
        if ($strDeviceType == '' || $strDeviceType == NULL) {
            $strDeviceType = 'BOT';
        }
        if ($tracker_type == 3) {
            $intAffId = $thr_sub1;
        }

        //get site flag
        $siteFlagMaster = SiteFlagMaster::select('id')
            ->where('site_flag_name', '=', $device)
            ->first();
        if (!empty($siteFlagMaster)) {
            $intSiteFlagId = $siteFlagMaster->id;
        }
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $strIp = $commonFunctionRepo->get_client_ip();
        $intADVSplitId = $commonFunctionRepo->getAdvertorialIdFromName($strFileName, $intSiteFlagId);

        $Advvisitor = AdvVisitor::select('id')
            ->whereDate('created_at', '=', Carbon::now()->toDateString())
            ->where('remote_ip', '=', $strIp)
            ->where('browser', '=', $strBrowserType)
            ->where('country', '=', $countryCode)
            ->where('adv_id', '=', $intADVSplitId)
            ->where('tracker_id', '=', $tracker_type)
            ->where('site_flag_id', '=', $intSiteFlagId);
        if ($tracker_type == 1) {
            $tracker_unique_id = $pixel;
        } else if ($tracker_type == 2) {
            $tracker_unique_id = $strTransid;
        } else if ($tracker_type == 3) {
            $tracker_unique_id = $thr_transid;
        } else if ($tracker_type == 7) {
            $tracker_unique_id = 0;
        } else {
            $tracker_unique_id = $strTransid;
        }
        $Advvisitor = $Advvisitor->where('tracker_unique_id', '=', $tracker_unique_id)
            ->first();

        if (!empty($Advvisitor)) {
            $intADVVisitorId = $Advvisitor->id;
            $visitorsCount = AdvVisitorsCount::where('adv_visitor_id', '=', $intADVVisitorId)
                ->where('adv_id', '=', $intADVSplitId)
                ->first();

            if ($visitorsCount->count() > 0) {
                $vCount = ( int )$visitorsCount->counts + 1;
                $test = $visitorsCount->update(array('counts' => $vCount));
                AdvVisitorsLastVisit::where('adv_visitor_id', '=', $intADVVisitorId)
                    ->update(array('last_visit_page' => $strFileName));

            } else {
                $objVisitorsCount = new AdvVisitorsCount;
                $objVisitorsCount->adv_visitor_id = $intADVVisitorId;
                $objVisitorsCount->count = 1;
                $objVisitorsCount->adv_id = $intADVSplitId;
                AdvVisitorsLastVisit::where('adv_visitor_id', '=', $intADVVisitorId)
                    ->update(array('last_visit_page' => $strFileName));
            }
        } else {
            $fullReferenceUrl = URL::full();
            $strExistingDomain = env('APP_URL');
            if ($strExistingDomain == '') {
                $strExistingDomain = $_SERVER['SERVER_NAME'];
            }

            $strRefererSite = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $domain_id = $commonFunctionRepo->getDomainId();
            $objVisitor = new AdvVisitor;
            $objVisitor->remote_ip = $strIp;
            $objVisitor->domain_id = $domain_id;
            $objVisitor->browser = $strBrowserType;
            $objVisitor->country = $countryCode;
            $objVisitor->referer_site = $strRefererSite;
            $objVisitor->existingdomain = $strExistingDomain;
            $objVisitor->resolution = $strScrResolution;
            $objVisitor->device_type = $strDeviceType;
            $objVisitor->user_agent = $u_agent;
            $objVisitor->tracker_unique_id = $tracker_unique_id;
            $objVisitor->site_flag_id = $intSiteFlagId;
            $objVisitor->tracker_id = $tracker_type;
            $objVisitor->adv_id = $intADVSplitId;
            $objVisitor->save();
            $intADVVisitorId = $objVisitor->id;

            //insert data into thrive details
            $thriveArr = [
                'adv_visitor_id' => $intADVVisitorId,
                'thr_source' => $thr_source,
                'thr_sub1' => $thr_sub1,
                'thr_sub2' => $thr_sub2,
                'thr_sub3' => $thr_sub3,
                'thr_sub4' => $thr_sub4,
                'thr_sub5' => $thr_sub5,
                'thr_sub6' => $thr_sub6,
                'thr_sub7' => $thr_sub7,
                'thr_sub8' => $thr_sub8,
                'thr_sub9' => $thr_sub9,
                'thr_sub10' => $thr_sub10
            ];

            AdvThriveDetail::create($thriveArr);

            //insert into adv_adtopiatails
            $adv_adtopia_details = [
                'adv_visitor_id' => $intADVVisitorId,
                'atp_source' => $atp_source,
                'atp_vendor' => $atp_vendor,
                'atp_sub1' => $atp_sub1,
                'atp_sub2' => $atp_sub2,
                'atp_sub3' => $atp_sub3,
                'pid' => $pid,
                'acid' => '',
                'cid' => '',
                'crvid' => ''
            ];

            AdvAdtopiaDetail::create($adv_adtopia_details);
            //insert into adv_last_visit
            $adv_last_visitor = [
                'adv_visitor_id' => $intADVVisitorId,
                'last_visit_page' => $strFileName
            ];
            AdvVisitorsLastVisit::create($adv_last_visitor);
            $adv_visitors_count = [
                'adv_visitor_id' => $intADVVisitorId,
                'counts' => 1,
                'adv_id' => $intADVSplitId
            ];
            AdvVisitorsCount::create($adv_visitors_count);

            $adv_pixel_firing = [
                'adv_visitor_id' => $intADVVisitorId,
                'page_type' => 1
            ];
            $ret = AdvPixelFiring::create($adv_pixel_firing);

        }
        return array('tracker_type' => $tracker_type, 'adv_visitor_id' => $intADVVisitorId);
    }

    /**
     * Updte last adv viisit
     *
     * @param $intAdvVisitorId
     * @param $strFileName
     * @return mixed
     */
    public static function updateLastAdvVisit($intAdvVisitorId, $strFileName)
    {
        $today_date = date('Y-m-d H:i:s');
        $affectedRows = AdvVisitorsLastVisit::where('adv_visitor_id', '=', $intAdvVisitorId)->update(['last_visit_page' => DB::raw("CONCAT(last_visit_page,'||','$strFileName')"), 'updated_at' => $today_date]);
        return $affectedRows;

    }

    /**
     * Check adv pixel status
     *
     * @param $intAdvVisitorId
     * @param string $page_type
     * @return bool
     */
    public static function checkAdvPixelStatus($intAdvVisitorId, $page_type = 'LP')
    {
        $chkStaus = AdvPixelFiring::where('adv_visitor_id', '=', $intAdvVisitorId)
            ->where('page_type', '=', $page_type)
            ->first();

        if ($chkStaus->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Update pixel status
     *
     * @param $intAdvVisitorId
     * @param $page_type
     * @return mixed
     */
    public static function updatePixelStatus($intAdvVisitorId, $page_type)
    {
        $adv_pixel_firing = [
            'adv_visitor_id' => $intAdvVisitorId,
            'page_type' => $page_type
        ];
        $ret = AdvPixelFiring::create($adv_pixel_firing);

        return $ret;
    }

    /**
     * Find tracker
     *
     * @param $request
     * @return array
     */
    public static function findTracker($request)
    {
        $trakerResult = [];
        $duRepo = new DynamicUrlRepository;

        $acid = (isset($request->acid)) ? $request->acid : '';
        $cid = (isset($request->cid)) ? $request->cid : '';
        $acsrc = (isset($request->acsrc)) ? $request->acsrc : '';
        $campaign = (isset($request->campaign)) ? $request->campaign : '';
        $thr_sub1 = (isset($request->thr_sub1)) ? $request->thr_sub1 : '';
        $aff_id = (isset($request->aff_id)) ? $request->aff_id : '';
        $atp_vendor = (isset($request->atp_vendor)) ? $request->atp_vendor : '';
        $atp_source = (isset($request->atp_source)) ? $request->atp_source : '';
        $atpEntValue = (isset($request->value)) ? $request->value : '';
        $pixel = (isset($request->pixel)) ? $request->pixel : '';

        if ($atpEntValue) {
            $adtopiaBasicValue = $duRepo->stringcrypt($atpEntValue, 'd');
            $data = explode('&', $adtopiaBasicValue);
            $adp_details = [];
            $return = [];
            foreach ($data as $key => $value) {
                $temp = '';
                $temp = explode('=', $value);
                $adp_details[$temp[0]] = $temp[1];
            }
            $acid = isset($adp_details['acid']) ? $adp_details['acid'] : '';
            $acsrc = isset($adp_details['acsrc']) ? $adp_details['acsrc'] : '';
        }

        $trakerResult['acid'] = $acid;
        $trakerResult['cid'] = $cid;
        $initial_tracker = (isset($request->tracker)) ? $request->tracker : '';
        $baseTracker = '';

        if ($initial_tracker) {
            $trackerArray = explode('-', $initial_tracker);
            if (isset($trackerArray[1])) {
                $tracker = strtoupper($trackerArray[1]);
                $tracker_type = strtoupper($trackerArray[0]);
                $baseTracker = strtoupper($tracker_type);

            } else {
                $tracker = strtoupper($trackerArray[0]);
                $tracker_type = strtoupper($trackerArray[0]);
                $baseTracker = strtoupper($tracker_type);
            }
        } else {
            $tracker = '';
            $tracker_type = '';
        }

        if ((!empty($acid) && !empty($cid)) || (!empty($pixel))) {
            $tracker_type = 'ADTOPIA';

            if (!empty($acsrc)) {
                $asrc = $acsrc;
                $arrTracker = $duRepo->trackerType_Vendor($asrc);
                $tracker_type = $arrTracker['tracker_type'];
                $atp_vendor = $arrTracker['atp_vendor'];
                $atp_source = (isset($request->acacnt)) ? $request->acacnt : '';
            }
        } else if (isset($request->thr_source)) {
            $tracker_type = 'THRIVE';
        } else if (substr($campaign, 0, 2) == 'FB') {
            $tracker_type = $campaign;
        } else if ($campaign == 'GDT') {
            $tracker_type = 'GDT';
        } else if (!empty($transid)) {
            $tracker_type = 'HO';
        } else if ($tracker_type == '') {
            $tracker_type = 'UN_KNOWN';
        }

        if ($tracker_type == 'THRIVE') {
            $aff_id = $thr_sub1;
        }

        $trakerResult['tracker_type'] = $tracker_type;
        $trakerResult['tracker'] = $tracker;
        $trakerResult['aff_id'] = $aff_id;
        $trakerResult['atp_vendor'] = $atp_vendor;
        $trakerResult['atp_source'] = $atp_source;
        $trakerResult['baseTracker'] = $baseTracker;

        return $trakerResult;
    }

}
