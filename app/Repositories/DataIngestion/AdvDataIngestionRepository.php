<?php


namespace App\Repositories\DataIngestion;


use App\Models\AdvAdtopiaDetail;
use App\Models\AdvClickDetails;
use App\Models\AdvPixelFiring;
use App\Models\AdvVisitor;
use App\Models\AdvVisitorsCount;
use App\Models\AdvVisitorsTemp;
use App\Models\DeviceSiteMaster;
use App\Models\SiteFlagMaster;
use App\Repositories\Interfaces\AdvDataIngestionInterface;
use App\Repositories\Interfaces\CommonFunctionsInterface;
use App\Repositories\Interfaces\PixelFireInterface;
use App\Repositories\Interfaces\UAInterface;
use App\Repositories\UARepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Models\AdvUuid;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\VisitorRepository;

/**
 * Class AdvDataIngestionRepository
 *
 * @package App\Repositories\DataIngestion
 */
class AdvDataIngestionRepository implements AdvDataIngestionInterface
{
    /**
     * AdvDataIngestionRepository constructor.
     * @param CommonFunctionsInterface $commonFunctionsInterface
     * @param PixelFireInterface $pixelFireInterface
     */
    public function __construct(CommonFunctionsInterface $commonFunctionsInterface, PixelFireInterface $pixelFireInterface, UAInterface $UAInterface)
    {
        $this->commonFunctionsInterface = $commonFunctionsInterface;
        $this->pixelFireInterface = $pixelFireInterface;
        $this->UAInterface = $UAInterface;
    }

    /**
     * Save adv visitor data
     *
     * @param $arrParamData
     * @param $arrParamVisitor
     */
    public function saveADVVisitorData($arrParamData, $arrParamVisitor, $page)
    {
        $data = $arrParamData;
        $visitor_parameters = $arrParamVisitor;
        $strIp = @$data['ip_address'];
        $date = @$visitor_parameters['date'];
        $tracker_id = @$visitor_parameters['tracker_id'];
        $strBrowserType = @$arrParamData['browser'];
        $client_id = @$visitor_parameters['client_id'];
        $tracker_type = @$visitor_parameters['tracker_id'];
        $countryCode = $data['country'];
        $page = $page . '.php';
        $page = str_replace('.php', '', $page);
        $strFileName = $page;
        $device = $data['site_flag_id'];
        $pixel = $data['pixel'];
        $strTransid = $data['transid'];
        $thr_transid = $data['thr_transid'];
        $strScrResolution = $data['scr_resolution'];
        $strDeviceType = $data['platform'];
        $intSiteFlagId = $data['site_flag'];
        $u_agent = $data['user_agent'];
        $tracker = $data['tracker'];
        $splitPath = $data['split_path'];
        $intAffId = $data['aff_id'];
        $intAffSub = $data['aff_sub'];
        $strOfferId = $data['offer_id'];
        $thr_sub1 = $data['thr_sub1'];
        $currentUrl = $data['existingdomain'];
        $domain_name = $data['domain_name'];
        $adv_uuid = $visitor_parameters['uuid'];
        $tracker_type = $this->defineTrackerType($data);
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
        $intADVSplitId = $this->commonFunctionsInterface->dynamicAdvertorialsAdd($strFileName, $splitPath, $domain_name);
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
        $siteFlagMaster = DeviceSiteMaster::select('id')
            ->where('device_site_name', '=', $device)
            ->first();
        if (!empty($siteFlagMaster)) {
            $intSiteFlagId = $siteFlagMaster->id;
        }
        //$intADVSplitId = $this->commonFunctionsInterface->getAdvertorialIdFromName($strFileName, $intSiteFlagId);
        /*$Advvisitor = AdvVisitor::select('id')
            ->whereDate('created_at', '=', $date)
            ->where('remote_ip', '=', $strIp)
            ->where('browser', '=', $strBrowserType)
            ->where('country', '=', $countryCode)
            ->where('adv_id', '=', $intADVSplitId)
            ->where('tracker_id', '=', $tracker_type)
            ->where('device_site_id', '=', $intSiteFlagId);*/

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
        // $Advvisitor               =  $Advvisitor->where( 'tracker_unique_id', '=', $tracker_unique_id )

        $Advvisitor = AdvUuid::select('adv_visitor_id as id')
            ->where('uuid', $adv_uuid);

        $Advvisitor = $Advvisitor->first();
        if (!empty($Advvisitor)) {
            $intADVVisitorId = $Advvisitor->id;
            $visitorsCount = AdvVisitorsCount::where('adv_visitor_id', '=', $intADVVisitorId)
                //->where( 'adv_id', '=', $intADVSplitId )
                ->first();
            if (($visitorsCount) && ($visitorsCount->count() > 0)) {
                $vCount = ( int )$visitorsCount->counts + 1;
                $test = $visitorsCount->update(array('counts' => $vCount));


            } else {
                $objVisitorsCount = new AdvVisitorsCount;
                $objVisitorsCount->adv_visitor_id = $intADVVisitorId;
                $objVisitorsCount->counts = 1;
                $objVisitorsCount->save();

            }
        } else {
            $fullReferenceUrl = URL::full();
            $strExistingDomain = $data['domain_name'];
            $strRefererSite = isset($data['refer_site']) ? $data['refer_site'] : '';
            $domain_id = $this->commonFunctionsInterface->getDomainId($strExistingDomain);
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
            $objVisitor->device_site_id = $intSiteFlagId;
            $objVisitor->tracker_id = $tracker_type;
            $objVisitor->adv_id = $intADVSplitId;
            $objVisitor->save();
            $intADVVisitorId = $objVisitor->id;
            $advVisitorTempObject = new AdvVisitorsTemp();
            $advVisitorTempObject->adv_visitor_id = $intADVVisitorId;
            $advVisitorTempObject->adv_id = $intADVSplitId;
            $advVisitorTempObject->tracker_id = $tracker_type;
            $advVisitorTempObject->device_site_id = $intSiteFlagId;
            $advVisitorTempObject->tracker_unique_id = $tracker_unique_id;
            $advVisitorTempObject->remote_ip = $strIp;
            $advVisitorTempObject->browser = $strBrowserType;
            $advVisitorTempObject->country = $countryCode;
            $advVisitorTempObject->device_type = $strDeviceType;
            $advVisitorTempObject->save();
            //insert into adv_uuid
            $uuid_check = AdvUuid::where('uuid', $adv_uuid)->first();
            if (!$uuid_check) {
                $adv_uuid_Object = new AdvUuid();
                $adv_uuid_Object->adv_visitor_id = $intADVVisitorId;
                $adv_uuid_Object->uuid = $adv_uuid;
                $adv_uuid_Object->save();
            }
            //insert into adv_adtopiatails
            $adv_adtopia_details = [
                'adv_visitor_id' => $intADVVisitorId,
                'atp_source' => $data['atp_source'],
                'atp_vendor' => $data['atp_vendor'],
                'atp_sub1' => $data['atp_sub1'],
                'atp_sub2' => $data['atp_sub2'],
                'atp_sub3' => $data['atp_sub3'],
                'pid' => $data['pid'],
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
            $adv_visitors_count = [
                'adv_visitor_id' => $intADVVisitorId,
                'counts' => 1,
            ];
            AdvVisitorsCount::create($adv_visitors_count);

            $adv_pixel_firing = [
                'adv_visitor_id' => $intADVVisitorId,
                'page_type' => 1
            ];
            $ret = AdvPixelFiring::create($adv_pixel_firing);

        }

        $flagAPVisit = $this->pixelFireInterface->getAdvPixelFireStatus("AP", $intADVVisitorId);
        $retrunArray['flagAPVisit'] = $flagAPVisit;
        $retrunArray['intAdvVisitorId'] = $intADVVisitorId;
        $retrunArray['tracker_type'] = $this->commonFunctionsInterface->getTrackerType($tracker_type);
        //die();
        $atplog = "0";
        $adtopiapixel = "";
        $response = "";
        $strResult = "";
        if (!$flagAPVisit) {
            if ($tracker_type == 1) {
                $chkArry = array(
                    "tracker_type" => $tracker_type,
                    "tracker" => $tracker,
                    "atp_vendor" => $data['atp_vendor'],
                    "pixel" => $pixel,
                    "pixel_type" => "AP",
                    "statusupdate" => "ADV",
                    "intVisitorId" => $intADVVisitorId,
                    "redirecturl" => $currentUrl
                );
                $arrResultDetail = $this->pixelFireInterface->atpPixelFire($chkArry);
                if ($arrResultDetail) {
                    $strResult = $arrResultDetail['result'];
                    $response = $arrResultDetail['result_detail'];
                    $adtopiapixel = $arrResultDetail['adtopiapixel'];
                }
            }
            $this->pixelFireInterface->setAdvPixelFireStatus("AP", $intADVVisitorId);
        }
    }

    /**
     *Save adv clicks
     *
     * @param $arrParamData
     * @param $arrParamVisitor
     * @return mixed
     */
    public function saveAdvClicks($arrParamData, $arrParamVisitor, $page)
    {
        $data = $arrParamData;
        $visitor_parameters = $arrParamVisitor;
        // if($data['action'] =  'AddClick'){

        $transid = isset($data['transid']) ? $data['transid'] : '';
        $aff_id = isset($data['aff_id']) ? $data['aff_id'] : '';
        $campaign_id = isset($data['campaign']) ? $data['campaign'] : '';
        // $split_name     = isset($data['file_name'])?$data['file_name']:'';
        $page = $page . '.php';
        $page = str_replace('.php', '', $page);
        $page = isset($page) ? $page : '';
        $split_name = $page;
        $cur_dt = Carbon::now()->format('Y-m-d H:i:s');
        $timeSpent = isset($data['timeSpent']) ? $data['timeSpent'] : '';
        $click_link = $data['click_link'];
        $screen = isset($data['screen']) ? $data['screen'] : '';
        $redirectUrl = $data['redirectUrl'];
        $linkUrl = isset($data['existingdomain']) ? $data['existingdomain'] : '';
        $intADVSplitId = $this->commonFunctionsInterface->getAdvertorialIdFromName($split_name, $intSiteFlagId = NULL);
        $Advvisitor = AdvUuid::select('adv_uuid.adv_visitor_id as id', 'adv_visitors.adv_id')
            ->join('adv_visitors', 'adv_uuid.adv_visitor_id', '=', 'adv_visitors.id')
            ->where('uuid', $visitor_parameters['uuid'])
            ->first();
        if (!empty($Advvisitor)) {
            $visitor_id = $Advvisitor->id;
            $chkExist = AdvClickDetails::where('adv_visitor_id', $visitor_id)->get();
            if (sizeof($chkExist->toArray()) > 0) {

            } else {
                $adv_click_details = [
                    'adv_visitor_id' => $visitor_id,
                    'adv_id' => $Advvisitor->adv_id,
                    'affiliated_id' => $aff_id,
                    'remote_ip' => @$data['ip_address'],
                    'date_time' => $cur_dt,
                    'time_spent' => $timeSpent,
                    'resolution' => $screen,
                    'click_link' => $click_link,
                    'split_name' => $split_name,
                    'link_url' => $linkUrl,
                    'page' => $page
                ];
                $ret = AdvClickDetails::create($adv_click_details);
                //return $ret;
                $arrUrlParams = array('thopecive', 'pixelblogger', 'siliconmarket', 'technoraven');
                foreach ($arrUrlParams as $eachUrls) {
                    if (strpos($redirectUrl, $eachUrls) !== false) {
                        $flagLPVisit = $this->pixelFireInterface->getAdvPixelFireStatus("LP", $visitor_id);
                        if (!$flagLPVisit) {


                        }
                    }
                }
                return $ret;
            }
        }

        // }
    }

    /**
     * Set agent visitor param
     *
     * @param $request
     * @param null $pageName
     * @return array
     */
    public function setAgentVisitorParam($request, $pageName = null)
    {

        $agentRequest = $request->user_agent;
        $agentStringRequest = array();
        parse_str($request->query_string, $agentStringRequest);
        // $agentStringRequest      =  $request->query_string;

        //  $ua 				= 	new UAClass();
        $uaRepoObject = new UARepository();
        $arrUserAgentInfo = $uaRepoObject->parse_user_agent($agentRequest);
        //  $arrUserAgentInfo 	= 	$ua->parse_user_agent($agentRequest);
        // Identify the user country
        // $splitPath			=	$request->root()."/".$request->path();
        $common_fn = new CommonFunctionsRepository();
        $strIp = $common_fn->get_client_ip();
        $splitPath = Str::before($request->existingdomain, '?');

        $countryCode = $arrUserAgentInfo['country'];
        $strSiteFlag = $arrUserAgentInfo['device'];
        $intSiteFlagId = $arrUserAgentInfo['siteFlagId'];
        $strBrowser = $arrUserAgentInfo['browser'];
        $strPlatform = $arrUserAgentInfo['platform'];
        $currentUrl = URL::full();
        $existingDomain = $request->existingdomain;//existing domain
        $intAffiliateId = 0;
        $strScrResolution = "";
        $strErrorMessage = "";
        $token_decoded = "";
        $ext_var2 = "";
        $intVisitorId = 0;
        $strTransid = (isset($agentStringRequest['transid'])) ? $agentStringRequest['transid'] : "";
        $strCampaign = (isset($agentStringRequest['campaign'])) ? $agentStringRequest['campaign'] : "";
        $strOfferId = (isset($agentStringRequest['aff_id'])) ? $agentStringRequest['aff_id'] : "";
        $intYlbAffId = (isset($agentStringRequest['test'])) ? $agentStringRequest['test'] : "";
        $intYlbAffSub = (isset($agentStringRequest['aff_sub'])) ? $agentStringRequest['aff_sub'] : "";
        $aff_sub2 = (isset($agentStringRequest['aff_sub2'])) ? $agentStringRequest['aff_sub2'] : "";
        $aff_sub3 = (isset($agentStringRequest['atp_sub3'])) ? $agentStringRequest['atp_sub3'] : "";
        $aff_sub4 = (isset($agentStringRequest['atp_sub4'])) ? $agentStringRequest['atp_sub4'] : "";
        $aff_sub5 = (isset($agentStringRequest['atp_sub5'])) ? $agentStringRequest['atp_sub5'] : "";

        $source = (isset($agentStringRequest['source'])) ? $agentStringRequest['source'] : "";
        $tid = (isset($agentStringRequest['tid'])) ? $agentStringRequest['tid'] : "";
        $pid = (isset($agentStringRequest['pid'])) ? $agentStringRequest['pid'] : "";

        $thr_source = (isset($agentStringRequest['thr_source'])) ? $agentStringRequest['thr_source'] : "";
        $thr_transid = (isset($agentStringRequest['thr_transid'])) ? $agentStringRequest['thr_transid'] : "";
        $thr_sub1 = (isset($agentStringRequest['thr_sub1'])) ? $agentStringRequest['thr_sub1'] : "";
        $thr_sub2 = (isset($agentStringRequest['thr_sub2'])) ? $agentStringRequest['thr_sub2'] : "";
        $thr_sub3 = (isset($agentStringRequest['thr_sub3'])) ? $agentStringRequest['thr_sub3'] : "";
        $thr_sub4 = (isset($agentStringRequest['thr_sub4'])) ? $agentStringRequest['thr_sub4'] : "";
        $thr_sub5 = (isset($agentStringRequest['thr_sub5'])) ? $agentStringRequest['thr_sub5'] : "";
        $thr_sub6 = (isset($agentStringRequest['thr_sub6'])) ? $agentStringRequest['thr_sub6'] : "";
        $thr_sub7 = (isset($agentStringRequest['thr_sub7'])) ? $agentStringRequest['thr_sub7'] : "";
        $thr_sub8 = (isset($agentStringRequest['thr_sub8'])) ? $agentStringRequest['thr_sub8'] : "";
        $thr_sub9 = (isset($agentStringRequest['thr_sub9'])) ? $agentStringRequest['thr_sub9'] : "";
        $thr_sub10 = (isset($agentStringRequest['thr_sub10'])) ? $agentStringReques['thr_sub10'] : "";

        $atp_source = (isset($agentStringRequest['atp_source'])) ? $agentStringRequest['atp_source'] : "";
        $atp_vendor = (isset($agentStringRequest['atp_vendor'])) ? $agentStringRequest['atp_vendor'] : "";
        $atp_sub1 = (isset($agentStringRequest['atp_sub1'])) ? $agentStringRequest['atp_sub1'] : "";
        $atp_sub2 = (isset($agentStringRequest['atp_sub2'])) ? $agentStringRequest['atp_sub2'] : "";
        $atp_sub3 = (isset($agentStringRequest['atp_sub3'])) ? $agentStringRequest['atp_sub3'] : "";
        $atp_sub4 = (isset($agentStringRequest['atp_sub4'])) ? $agentStringRequest['atp_sub4'] : "";
        $atp_sub5 = (isset($agentStringRequest['atp_sub5'])) ? $agentStringRequest['atp_sub5'] : "";
        ###  Extra details parameter ##
        $ext_var1 = (isset($agentStringRequest['ext_var1'])) ? $agentStringRequest['ext_var1'] : "";//vendorclick id adtopia
        $ext_var2 = (isset($agentStringRequest['ext_var2'])) ? $agentStringRequest['ext_var2'] : "";
        $ext_var3 = (isset($agentStringRequest['ext_var3'])) ? $agentStringRequest['ext_var3'] : "";
        $ext_var4 = (isset($agentStringRequest['ext_var4'])) ? $agentStringRequest['ext_var4'] : "";
        $ext_var5 = (isset($agentStringRequest['ext_var5'])) ? $agentStringRequest['ext_var5'] : "";
        ###  Extra details parameter ##
        $tracker = (isset($agentStringRequest['tracker'])) ? $agentStringRequest['tracker'] : "";
        $pixel = (isset($agentStringRequest['pixel'])) ? $agentStringRequest['pixel'] : "";

        $acid = (isset($agentStringRequest['acid'])) ? $agentStringRequest['acid'] : "";
        $cid = (isset($agentStringRequest['cid'])) ? $agentStringRequest['cid'] : "";
        $crvid = (isset($agentStringRequest['crvid'])) ? $agentStringRequest['crvid'] : "";
        $acacnt = (isset($agentStringRequest['acacnt'])) ? $agentStringRequest['acacnt'] : "";
        $acsrc = (isset($agentStringRequest['acsrc'])) ? $agentStringRequest['acsrc'] : "";

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $strDomainName = $request->domain_name;
        $strRefererSite = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        ## YLB tracking campaign
        if ($strCampaign != '') {
            $intAffiliateId = $this->commonFunctionsInterface->getCampaignAffID($strCampaign, $intSiteFlagId, $strOfferId);
        }
        ## LP Duplication Checking
        $ext_var2 = $token_decoded = '';
        if (!isset($agentStringRequest['ext_var2']) && isset($agentStringRequest['token'])) {
            $atp_token = (isset($agentStringRequest['token'])) ? $agentStringRequest['token'] : "";
            $token_decoded = $this->commonFunctionsInterface->stringcrypt($atp_token, 'd');
            $current_time = Carbon::now();
            $from_time = strtotime($token_decoded);
            $to_time = strtotime($current_time);
            $time_diff = round(abs($to_time - $from_time));//. " seconds";
            if ($time_diff > 300) {
                $ext_var2 = '1';
            } else {
                $ext_var2 = '0';
            }
        } else if (isset($agentStringRequest['ext_var2'])) {
            $ext_var2 = $agentStringRequest['ext_var2'];
        } else if ((strtoupper($tracker) == "ADTOPIA" || strtoupper($tracker) == "ADTOPIA2") && !isset($agentStringRequest['token'])) {
            $ext_var2 = '1';
        }

        // $splitName = '';
        //Define array parameters for visitor Id creation
        $arrParam = array(
            "file_name" => $pageName,
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
            "existingdomain" => $existingDomain,
            "user_agent" => $user_agent,
            "domain_name" => $strDomainName,
            "refer_site" => $strRefererSite,
            "ip_address" => $strIp
        );
        return $arrParam;
    }

    /**
     * Set adv agent request
     *
     * @param $request
     * @param $arrParam
     * @param $uuid
     * @return array
     */
    public static function setAdvAgentRequest($request, $arrParam, $uuid)
    {
        $u_agent = $request['user_agent'];
        $ua = new UARepository();
        $arrUserAgentInfo = $ua->parse_user_agent($u_agent);
        // Identify the user country

        $countryCode = $arrUserAgentInfo['country'];
        $intSiteFlagId = $arrUserAgentInfo['siteFlagId'];
        $strSiteFlag = $arrUserAgentInfo['device'];
        $strBrowser = $arrUserAgentInfo['browser'];
        $common_fn = new CommonFunctionsRepository();
        $strIp = $common_fn->get_client_ip();
        $Visitor_class = new VisitorRepository();
        $tracker_type = $Visitor_class->defineTrackerType($arrParam);
        $strTransid = $arrParam['transid'];
        $thr_transid = ($arrParam['thr_transid'] != '') ? $arrParam['thr_transid'] : '';
        $pixel = ($arrParam['pixel'] != '') ? $arrParam['pixel'] : '';
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
        $date = Carbon::now()->toDateString();


        $request_array = array(
            "ip_address" => $strIp,
            "date" => $date,
            "tracker_id" => $tracker_type,
            "browser" => $strBrowser,
            "client_id" => "CFP",
            "tracker_unique_id" => $tracker_unique_id,
            "device_type" => $strSiteFlag,
            //   "page"=>$page,
            "uuid" => $uuid
        );
        return $request_array;
    }

    /**
     * Define tracker type
     *
     * @param $arrParam
     * @return int
     */
    public function defineTrackerType($arrParam)
    {
        //1.ADTOPIA,2.HO,3.THRIVE,4.FB,5.GDT,6.Direct,7.UN_KNOWN
        if (isset($arrParam['pixel']) && !empty($arrParam['pixel'])) {
            //$tracker_type = "ADTOPIA";
            $tracker_type = 1;
        } else if (isset($arrParam['thr_source']) && !empty($arrParam['thr_source'])) {
            //$tracker_type = "THRIVE";
            $tracker_type = 3;
        } else if (substr($arrParam['campaign'], 0, 2) == 'FB') {
            $tracker_type = $arrParam['campaign'];
        } else if ($arrParam['campaign'] == 'GDT') {
            //$tracker_type = "GDT";
            $tracker_type = 5;
        } else if (!empty($arrParam['transid'])) {
            //$tracker_type = "HO";
            $tracker_type = 2;
        } else if ($arrParam['tracker'] == 'Direct') {
            //$tracker_type = "Direct";
            $tracker_type = 6;
        } else {
            //$tracker_type = "UN_KNOWN";
            $tracker_type = 7;
        }
        return $tracker_type;
    }

}
