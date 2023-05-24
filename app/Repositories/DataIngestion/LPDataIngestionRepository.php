<?php

namespace App\Repositories\DataIngestion;

use App\Models\Skip;
use App\Models\SplitUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Repositories\UARepository;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\Interfaces\CommonFunctionsInterface;
use App\Repositories\Interfaces\PixelFireInterface;
use App\Repositories\Interfaces\VisitorInterface;
use App\Repositories\Interfaces\PDFGenerationInterface;
use App\Models\AdvVisitor;
use App\Models\Visitor;
use App\Models\SplitInfo;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Str;
use App\Repositories\Interfaces\LPDataIngestionInterface;
use App\Repositories\Interfaces\UAInterface;
use App\Repositories\Interfaces\UserInterface;
use App\Repositories\Interfaces\LogInterface;


class LPDataIngestionRepository implements LPDataIngestionInterface
{
    /**
     * UAinterface
     *
     * @var UAInterface
     */
    private $ua_repo;

    /**
     * LPDataIngestionRepository constructor.
     *
     * @param VisitorInterface $visitorInterface
     * @param PixelFireInterface $pixelFireInterface
     * @param UAInterface $ua_repo
     * @param UserInterface $user_repo
     * @param CommonFunctionsInterface $commonFunctionsInterface
     * @param LogInterface $logInterface
     * @param PDFGenerationInterface $pdf_generation_repo
     */
    public function __construct(VisitorInterface $visitorInterface, PixelFireInterface $pixelFireInterface, UAInterface $ua_repo, UserInterface $user_repo, CommonFunctionsInterface $commonFunctionsInterface, LogInterface $logInterface, PDFGenerationInterface $pdf_generation_repo)
    {
        $this->visitorInterface = $visitorInterface;
        $this->pixelFireInterface = $pixelFireInterface;
        $this->ua_repo = $ua_repo;
        $this->user_repo = $user_repo;
        $this->commonFunctionsInterface = $commonFunctionsInterface;
        $this->logInterface = $logInterface;
        $this->pdf_generation_repo = $pdf_generation_repo;
    }

    /**
     * Set LLP param
     *
     * @param $request
     * @return array
     */
    public static function setLPParam($request)
    {

        $agentStringRequest = array();
        parse_str($request->query_string, $agentStringRequest);
        $ua_repo = new UARepository;
        $arrUserAgentInfo = $ua_repo->parse_user_agent();
        //Array to save visitor id and split info id
        $return_arr = array();
        // Identify the user country
        $splitPath = Str::before($request->existingdomain, '?');
        $countryCode = $arrUserAgentInfo['country'];
        $strSiteFlag = $arrUserAgentInfo['device'];
        $intSiteFlagId = $arrUserAgentInfo['siteFlagId'];
        $strBrowser = $arrUserAgentInfo['browser'];
        $strPlatform = $arrUserAgentInfo['platform'];
        //insertion to split_info table and domain details table
        $currentUrl = URL::full();
        $intAffiliateId = 0;
        $strScrResolution = '';
        $strErrorMessage = '';
        $token_decoded = '';
        $ext_var2 = '';
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
        $atp_sub6 = (isset($agentStringRequest['atp_sub6'])) ? $agentStringRequest['atp_sub6'] : "";
        //media_vendor parameter
        $media_vendor = (isset($agentStringRequest['media_vendor'])) ? $agentStringRequest['media_vendor'] : "";
        ###  Extra details parameter ##
        $ext_var1 = (isset($agentStringRequest['ext_var1'])) ? $agentStringRequest['ext_var1'] : ""; //vendorclick id adtopia
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

        $redirectDomain = (isset($agentStringRequest['domain'])) ? $agentStringRequest['domain'] : "";
        $adv_vis_id = (isset($agentStringRequest['adv_vis_id'])) ? $agentStringRequest['adv_vis_id'] : "";
        $adv_page = (isset($agentStringRequest['adv_page'])) ? $agentStringRequest['adv_page'] : "";
        $existingDomain = ($request->has('existingdomain')) ? $request->existingdomain : '';
        $useragent = ($request->has('user_agent')) ? $request->user_agent : '';
        $domainName = ($request->has('domain_name')) ? $request->domain_name : '';
        $refererSite = ($request->has('referer_site')) ? $request->referer_site : '';
        $common_repo = new CommonFunctionsRepository;

        if (isset($request['adv_page_name'])) {
            $adv_page = $request['adv_page_name'] . ".php";

            $intADVId = $common_repo->getAdvertorialIdFromName($adv_page, $intSiteFlagId = NULL);
        } else {
            $intADVId = 0;
        }
        ## YLB tracking campaign
        if ($strCampaign != '') {
            $intAffiliateId = $common_repo->getCampaignAffID($strCampaign, $intSiteFlagId, $strOfferId);
        }
        ## LP Duplication Checking
        $ext_var2 = $token_decoded = '';
        if (!isset($agentStringRequest['ext_var2']) && isset($agentStringRequest['token'])) {
            $atp_token = (isset($agentStringRequest['token'])) ? $agentStringRequest['token'] : "";
            $token_decoded = $common_repo->stringcrypt($atp_token, 'd');
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
        } else if (isset($agentStringRequest['ext_var2'])) {
            $ext_var2 = $agentStringRequest['ext_var2'];
        } else if ((strtoupper($tracker) == "ADTOPIA" || strtoupper($tracker) == "ADTOPIA2") && !isset($agentStringRequest['token'])) {
            $ext_var2 = '1';
        }

        $strIp = $common_repo->get_client_ip();
        //Define array parameters for visitor Id creation
        $arrParam = array(
            //  'file_name'         => $pageName,
            'split_path' => $splitPath,
            'affiliate_id' => $intAffiliateId,
            'transid' => $strTransid,
            'site_flag_id' => $strSiteFlag,
            'scr_resolution' => $strScrResolution,
            'country' => $countryCode,
            "ip_address" => @$strIp,
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
            'atp_sub6' => $atp_sub6,
            'media_vendor' => $media_vendor,
            'ext_var1' => $ext_var1,
            'ext_var2' => $ext_var2,
            'ext_var3' => $ext_var3,
            'ext_var4' => $ext_var4,
            'ext_var5' => $ext_var5,
            'adv_vis_id' => $adv_vis_id,
            'adv_page' => $adv_page,
            'redirectDomain' => $redirectDomain,
            'domain_name' => $domainName,
            "existingdomain" => $existingDomain,
            "referer_site" => $refererSite,
            'adv_page_name' => $intADVId,
            'user_agent' => $useragent
        );

        return $arrParam;
    }

    /**
     * Common splits
     *
     * @param $data
     * @param $visitorParam
     * @param $currentTime
     * @param $pageName
     * @param $queryString
     * @return mixed
     */
    public function commonSplits($data, $visitorParam, $currentTime, $pageName, $queryString)
    {
        $query = array();
        parse_str($queryString, $query);
        $request_query = (object)$query;
        $request = (object)array_merge($data, $query);
        $arrParam = array(
            'split_path' => $data['split_path'],
            'affiliate_id' => $data['affiliate_id'],
            'transid' => $data['transid'],
            'device_site_id' => $data['site_flag_id'],
            'scr_resolution' => $data['scr_resolution'],
            'country' => $data['country'],
            'ip_address' => isset($data['ip_address']) ? $data['ip_address'] : '',
            'browser' => $data['browser'],
            'platform' => $data['platform'],
            'site_flag' => $data['site_flag'],
            'aff_id' => $data['aff_id'],
            'aff_sub' => $data['aff_sub'],
            'offer_id' => $data['offer_id'],
            'aff_sub2' => $data['aff_sub2'],
            'aff_sub3' => $data['aff_sub3'],
            'aff_sub4' => $data['aff_sub4'],
            'aff_sub5' => $data['aff_sub5'],
            'campaign' => $data['campaign'],
            'source' => $data['source'],
            'tid' => $data['tid'],
            'pid' => $data['pid'],
            'thr_source' => $data['thr_source'],
            'thr_transid' => $data['thr_transid'],
            'thr_sub1' => $data['thr_sub1'],
            'thr_sub2' => $data['thr_sub2'],
            'thr_sub3' => $data['thr_sub3'],
            'thr_sub4' => $data['thr_sub4'],
            'thr_sub5' => $data['thr_sub5'],
            'thr_sub6' => $data['thr_sub6'],
            'thr_sub7' => $data['thr_sub7'],
            'thr_sub8' => $data['thr_sub8'],
            'thr_sub9' => $data['thr_sub9'],
            'thr_sub10' => $data['thr_sub10'],
            'pixel' => $data['pixel'],
            'tracker' => $data['tracker'],
            'atp_source' => $data['atp_source'],
            'atp_vendor' => $data['atp_vendor'],
            'atp_sub1' => $data['atp_sub1'],
            'atp_sub2' => $data['atp_sub2'],
            'atp_sub3' => $data['atp_sub3'],
            'atp_sub4' => $data['atp_sub4'],
            'atp_sub5' => $data['atp_sub5'],
            'media_vendor' => $data['media_vendor'],
            'ext_var1' => $data['ext_var1'],
            'ext_var2' => $data['ext_var2'],
            'ext_var3' => $data['ext_var3'],
            'ext_var4' => $data['ext_var4'],
            'ext_var5' => $data['ext_var5'],
            'adv_vis_id' => $data['adv_vis_id'],
            'existingdomain' => $data['existingdomain'],
            'domain_name' => $data['domain_name'],
            "referer_site" => $data['referer_site'],
            'adv_page' => $data['adv_page'],
            'redirectDomain' => $data['redirectDomain'],
            'user_agent' => $data['user_agent'],
            'split_uuid' => $visitorParam['uuid'],
        );
        $splitName = $pageName . '.php';
        $splitName = str_replace('.php', '', $splitName);
        $page = $splitName;
        $arrParam['file_name'] = $page;
        //Temporaray removed
        $visitors = $this->visitorInterface->saveVisitor($arrParam, $currentTime);
        $intVisitorId = $visitors['visitor_id'];
        $tracker_type = $visitors['tracker_type'];
        $flagLPVisit = $this->pixelFireInterface->getPixelFireStatus('LP', $intVisitorId);
        $atplog = '0';
        $adtopiapixel = '';

        $response = '';
        $strResult = '';
        $common_repo = new CommonFunctionsRepository;
        if (isset($request->adv_page_name)) {
            $adv_page = $request->adv_page_name;

            $intADVId = $common_repo->getAdvertorialIdFromName($adv_page, $intSiteFlagId = NULL);
        } else {
            $intADVId = 0;
        }
        if (!$flagLPVisit) {
            if ($tracker_type == 1) {
                $chkArry = array(
                    'tracker_type' => $tracker_type,
                    'tracker' => $data['tracker'],
                    'atp_vendor' => $data['atp_vendor'],
                    'pixel' => $data['pixel'],
                    'pixel_type' => 'LP',
                    'statusupdate' => 'SPLIT',
                    'intVisitorId' => $intVisitorId,
                    'redirecturl' => $data['existingdomain']
                );
                $arrResultDetail = $this->pixelFireInterface->atpPixelFire($chkArry);
                if ($arrResultDetail) {
                    $strResult = $arrResultDetail['result'];
                    $response = $arrResultDetail['result_detail'];
                    $adtopiapixel = $arrResultDetail['adtopiapixel'];
                }
            }
            $this->pixelFireInterface->setPixelFireStatus('LP', $intVisitorId);
            return $intVisitorId;
        }
    }
    /**
     * Store
     *
     * @param $data
     * @param $data_query
     * @param $params
     * @param $currentTime
     * @param $pageName
     * @param $visitorData
     */
    public function store($data, $data_query, $params, $currentTime, $pageName, $visitorData)
    {
       
        $query = array();
        parse_str($data_query, $query);

        $request_query = (object)$query;

        $request = (object)array_merge($data, $query);
        $domain_name = $visitorData['domain_name'];
        $params['page'] = $pageName;
        $split_id = SplitInfo::where('split_name', '=', $params['page'])->first();
        $request->split_info_id = (string)$split_id->id;
        //get visitor id
        $visitor = SplitUuid::where(['uuid' => $params['uuid']])->first();
        $intVisitorId = isset($visitor->visitor_id) ? $visitor->visitor_id : null;
        if ($intVisitorId && !empty($intVisitorId)) {
            $request->visitor_id = (string)$intVisitorId;
        } else {

            $request->visitor_id = Self::commonSplits($visitorData, $params, $currentTime, $pageName, $data_query);
        }
        $recordStatus = $this->commonFunctionsInterface->isTestLiveEmail($request->txtEmail);
        $user_exist = User::where('email', '=', $request->txtEmail)->where('telephone', '=', $request->txtPhone)->first();

        if (!$user_exist || $recordStatus == 'TEST') {
            $recordStatus = $this->commonFunctionsInterface->isTestLiveEmail($request->txtEmail);

            $arrResponse = $this->user_repo->storeUser($request, $recordStatus, $currentTime, $domain_name);
           // dd($arrResponse);
            //echo "======================USERARRAY==============";
            //print_r($arrResponse);die();
            //log for visitors parameters
            $strFileContent = '\n----------\n Date: ' . date('Y-m-d H:i:s') . "\n Form Submit - Visitors Parameters : " . json_encode($params) . '  \n';
            $logWrite = $this->logInterface->writeLog('-getvisitorsParameters', $strFileContent);

            if (!$arrResponse) {
                return null;
            }
            
            //Update uuid in users table
            $user = User::find($arrResponse['userId']);
            $user->user_uuid = $params['uuid'];
            $user->save();
            
            $intUserId = $arrResponse['userId'];
           // echo "USERID";
            //print_r($intUserId);die();
            $addToHistory = $this->user_repo->storeHistory($intUserId);
            $this->user_repo->storeQuestionsHistory($intUserId);
            //$this->pdf_generation_repo->generateEngagementPDF($intUserId);
            
        }
        
    }
    /**
     * Update adv id
     *
     * @param $data
     * @param $visitorParam
     */
    public function updateAdvId($data, $visitorParam)
    {
        $data = $data;
        $visitorParam = $visitorParam;
        $visitor_id = $data['visitor_id'];
        $adv_id = $data['adv_id'];
        // DB::enableQueryLog();
        $advVisitor = AdvVisitor::select('id')
            ->whereDate('created_at', '=', $visitorParam['date'])
            ->where('remote_ip', '=', $visitorParam['ip_address'])
            ->where('browser', '=', $visitorParam['browser'])
            ->where('tracker_id', '=', $visitorParam['tracker_id'])
            ->where('tracker_unique_id', '=', $visitorParam['tracker_unique_id'])
            ->where('adv_id', '=', $adv_id)
            ->first();
        if (isset($advVisitor->id)) {

            $saveAdvId = Visitor::where('id', $visitor_id)->update(['adv_visitor_id' => $advVisitor->id]);
        }
    }
    /**
     * Set agent visitor param
     *
     * @param $request
     * @param $pageName
     * @return array
     */
    public static function setAgentVisitorParam($request, $pageName)
    {
        $ua = new UARepository();
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
        $currentUrl = URL::full();
        $existingDomain = $request->existingdomain; //existing domain
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
        ##media_vendor parameter
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
        if (isset($request['adv_page_name'])) {
            $adv_page = $request['adv_page_name'] . ".php";

            $intADVId = CommonFunctions::getAdvertorialIdFromName($adv_page, $intSiteFlagId = NULL);
        } else {
            $intADVId = 0;
        }
        ## YLB tracking campaign
        if ($strCampaign != '') {
            $intAffiliateId = CommonFunctions::getCampaignAffID($strCampaign, $intSiteFlagId, $strOfferId);
        }
        ## LP Duplication Checking
        $ext_var2 = $token_decoded = '';
        if (!$request->has('ext_var2') && $request->has('token')) {
            $atp_token = $request->has('token') ? $request->token : '';
            $token_decoded = CommonFunctions::stringcrypt($atp_token, 'd');
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
        $common_fn = new CommonFunctionsRepository();
        $strIp = $common_fn->get_client_ip();
        //Define array parameters for visitor Id creation
        $arrParam = array(
            'file_name' => $pageName,
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
            'adv_page_name' => $intADVId,
            "existingdomain" => $existingDomain,
            'ip_address' => $strIp
        );

        return $arrParam;
    }
}
