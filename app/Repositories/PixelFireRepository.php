<?php

namespace App\Repositories;

use App\Models\VisitorsJourney;
use App\Models\AdvPixelFiring;
use App\Models\VendorPixelFiring;
use App\Models\FollowupVendorPixelFiring;
use App\Repositories\Interfaces\PixelFireInterface;
use App\Repositories\CommonFunctionsRepository;
use Carbon\Carbon;

/**
 * Class PixelFireRepository
 *
 * @package App\Repositories
 */
class PixelFireRepository implements PixelFireInterface
{
    /**
     * PixelFireRepository constructor.
     */
    public function __construct()
    {
        $this->commonFunctionRepo = new CommonFunctionsRepository;
    }

    /**
     * Get pixel fire status
     *
     * @param null $pixelType
     * @param null $intVisitorId
     * @param null $intUserId
     * @return false
     */
    public function getPixelFireStatus($pixelType = NULL, $intVisitorId = NULL, $intUserId = NULL)
    {
        if (is_null($pixelType)) {
            $pixelType = 'LP';
        }
        if (empty($intVisitorId)) {
            return false;
        }
        $visitorsPixelFiring = VisitorsJourney::where('visitor_id', '=', $intVisitorId)
            ->where('page_type', '=', $pixelType)
            ->select('page_type')
            ->first();
        if (!empty($visitorsPixelFiring)) {
            return $visitorsPixelFiring->page_type;
        } else {
            return false;
        }
    }

    /**
     * Get  followup pixel fire status
     *
     * @param null $pixelType
     * @param null $flvvisit_id
     * @return false
     */
    public function getFollowupPixelFireStatus($pixelType = NULL, $flvvisit_id = NULL)
    {
        if (is_null($pixelType)) {
            $pixelType = 'LP';
        }
        if (empty($flvvisit_id)) {
            return false;
        }
        $followupPixelFiring = FollowupVendorPixelFiring::where('followup_visit_id', '=', $flvvisit_id)
            ->where('page_type', '=', $pixelType)
            ->select('page_type')
            ->first();
        if (!empty($followupPixelFiring)) {
            return $followupPixelFiring->page_type;
        } else {
            return false;
        }
    }

    /**
     * Set pixel fire status
     *
     * @param null $pixelType
     * @param null $intVisitorId
     * @param null $intUserId
     * @return bool
     */
    public function setPixelFireStatus($pixelType = NULL, $intVisitorId = NULL, $intUserId = NULL)
    {
        if (is_null($intVisitorId)) $intVisitorId = 0;
        if (is_null($intUserId)) $intUserId = 0;
        if (is_null($pixelType)) $pixelType = 'LP';
        if (empty($intVisitorId)) {
            return false;
        }
        if ($pixelType == 'LP') {
            $flUp = VisitorsJourney::where('visitor_id', '=', $intVisitorId)->first();
            if (!empty($flUp)) {
                VisitorsJourney::where('visitor_id', '=', $intVisitorId)
                    ->whereNull('page_type')
                    ->update(array('page_type' => 'LP'));
            }
        } else {
            //TY, CN, SN, QP1, QP2
            $flUp = new VisitorsJourney;
            $flUp->visitor_id = $intVisitorId;
            $flUp->user_id = $intUserId;
            $flUp->page_type = $pixelType;
            $flUp->save();
        }
        if (@$flUp->id) {
            return true;
        } else {
            return false;
        }
    }
    ///////--anvis---add.start

    /**
     * Get adv pixel fire status
     *
     * @param null $pixelType
     * @param null $intVisitorId
     * @param null $intUserId
     * @return false
     */
    public function getAdvPixelFireStatus($pixelType = NULL, $intVisitorId = NULL, $intUserId = NULL)
    {
        if (is_null($pixelType)) $pixelType = "AP";
        if (empty($intVisitorId)) {
            return false;
        }
        $visitorsPixelFiring = AdvPixelFiring::where("adv_visitor_id", '=', $intVisitorId)
            ->where('page_type', '=', $pixelType)
            ->select('page_type')
            ->first();
        if (!empty($visitorsPixelFiring)) {
            return $visitorsPixelFiring->page_type;
        } else {
            return false;
        }
    }

    /**
     * Set adv pixel fire status
     *
     * @param null $pixelType
     * @param null $intVisitorId
     * @param null $intUserId
     * @return bool
     */
    public function setAdvPixelFireStatus($pixelType = NULL, $intVisitorId = NULL, $intUserId = NULL)
    {
        if (is_null($intVisitorId)) $intVisitorId = 0;
        if (is_null($intUserId)) $intUserId = 0;
        if (is_null($pixelType)) $pixelType = "AP";
        if (empty($intVisitorId)) {
            return false;
        }
        if ($pixelType == 'AP') {
            $flUp = AdvPixelFiring::where("adv_visitor_id", "=", $intVisitorId)->first();
            if (!empty($flUp)) {
                AdvPixelFiring::where("adv_visitor_id", "=", $intVisitorId)->update(array("page_type" => "AP"));
            }
        } else { //TY,CN,SN,QP1,QP2
            $flUp = new AdvPixelFiring;
            $flUp->adv_visitor_id = $intVisitorId;
            $flUp->page_type = $pixelType;
            $flUp->save();
        }
        if (@$flUp->id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update into pixel fire log adv
     *
     * @param $intAdvVisitorId
     * @param null $dbConn
     */
    
    //////--anvis---add.end

    /**
     * Atp pixel fire
     *
     * @param $chkArry
     * @return array
     */
    public function atpPixelFire($chkArry)
    {
        extract($chkArry);
        $arrResultDetail['result'] = $arrResultDetail['result_detail'] = $arrResultDetail['adtopiapixel'] = '';
        if (!isset($statusupdate)) {
            $statusupdate = '';
        }
        if ($tracker_type == '1') {
            $arrUrlParams = array(
                'pixel' => $pixel,
                'from_page' => $pixel_type,
                'domain_visitor' => $intVisitorId,
                'atp_vendor' => @$atp_vendor,
                'upid' => env('ADTOPIA_UPID'),
                'add_date'    => @$currentTime,
            );

            if ($pixel_type == 'LP') {
                $arrUrlParams['redirecturl'] = $redirecturl;
            } else if ($pixel_type == 'TY') {
                $arrUrlParams['cake_status'] = $cakePostStatus;
                $arrUrlParams['is_test'] = $record_status;
                $arrUrlParams['buyer_id'] = $buyer_id;
                $arrUrlParams['pay_in'] = $revenue;
                $arrUrlParams['currency'] = $currency;
                $arrUrlParams['vender_pixel_status'] = $intVoluumtrk2PixelFired;
            }
            $appEnv = env('APP_ENV');
            
            if ($appEnv == 'live') {
                $adtopiapixel = $this->commonFunctionRepo->creatURL('https://api.adtopiaglobal.com/global_pixel', $arrUrlParams);
            } elseif ($appEnv == 'pre') {
                $adtopiapixel = $this->commonFunctionRepo->creatURL('https://pre.api.adtopiaglobal.com/global_pixel', $arrUrlParams);
            } else {
                $adtopiapixel = $this->commonFunctionRepo->creatURL('https://dev.api.adtopiaglobal.com/global_pixel', $arrUrlParams);
            }

            $arrResultDetail = $this->commonFunctionRepo->fileGetContentAdtopia($adtopiapixel, 'adtopia_' . $pixel_type . '_curl_info', 'post', $arrUrlParams);
            $arrResultDetail['adtopiapixel'] = $adtopiapixel;
        }
        //if tracker = 1
        if ($tracker_type == '1') {
            if ($pixel_type == 'LP') {
                $vpFiringObj = new VendorPixelFiring();
                $vpFiringObj->visitor_id = $intVisitorId;
                $vpFiringObj->vendor = 'adtopia';
                $vpFiringObj->page_type = $pixel_type;
                $vpFiringObj->pixel_type = 'web';
                $vpFiringObj->pixel_log = $arrResultDetail['result_detail'];
                $vpFiringObj->pixel_result = $arrResultDetail['result'];
                $vpFiringObj->created_at    = @$currentTime;
                $vpFiringObj->save();
            } else {
                if (!isset($intUserId)) {
                    $intUserId = null;
                }
                $vpFiringObj = new VendorPixelFiring();
                $vpFiringObj->visitor_id = $intVisitorId;
                $vpFiringObj->user_id = @$intUserId;
                $vpFiringObj->vendor = 'adtopia';
                $vpFiringObj->page_type = $pixel_type;
                $vpFiringObj->pixel_type = 'web';
                $vpFiringObj->pixel_log = $arrResultDetail['result_detail'];
                $vpFiringObj->pixel_result = $arrResultDetail['result'];
                $vpFiringObj->created_at    = @$currentTime;
                $vpFiringObj->save();
            }
        }
        ## Update Advertorial Vendor pixel fire status
        if ($statusupdate == 'ADV') {
            Static::setAdvPixelFireStatus($intVisitorId);
        } else if ($statusupdate == 'SPLIT') {
            if (!isset($intUserId)) {
                $intUserId = 0;
            }
            $flagPageVisit = Static::getPixelFireStatus($pixel_type, $intVisitorId);
            if (!$flagPageVisit) {
                Static::setPixelFireStatus($pixel_type, $intVisitorId, $intUserId);
            }
        }
        return $arrResultDetail;
    }

    /**
     * Atp followup pixel fire
     *
     * @param $chkArry
     * @return array
     */
    public function atpFollowupPixelFire($chkArry)
    {
        extract($chkArry);
        $intVisitorId = $chkArry['intVisitorId'];
        $intUserId = $chkArry['user_id'];
        $arrResultDetail['result'] = $arrResultDetail['result_detail'] = $arrResultDetail['adtopiapixel'] = '';
        if (!isset($statusupdate)) {
            $statusupdate = '';
        }
        $tracker_type = '1';
        if ($tracker_type == '1') {
            $arrUrlParams = array(
                'pixel' => $pixel,
                'from_page' => $pixel_type,
                'domain_visitor' => $intVisitorId,
                'atp_vendor' => @$atp_vendor,
                'upid' => env('ADTOPIA_UPID'),
                'add_date'     => @$currentTime,
            );
            if ($pixel_type == 'LP') {
                $arrUrlParams['redirecturl'] = $redirecturl;
            } else if ($pixel_type == 'TY') {
                $arrUrlParams['redirecturl'] = $redirecturl;
            }
            $appEnv = env('APP_ENV');
            

            if ($appEnv == 'live') {
                $adtopiapixel = $this->commonFunctionRepo->creatURL('https://api.adtopiaglobal.com/global_pixel', $arrUrlParams);
            } elseif ($appEnv == 'pre') {
                $adtopiapixel = $this->commonFunctionRepo->creatURL('https://pre.api.adtopiaglobal.com/global_pixel', $arrUrlParams);
            } else {
                $adtopiapixel = $this->commonFunctionRepo->creatURL('https://dev.api.adtopiaglobal.com/global_pixel', $arrUrlParams);
            }

            $arrResultDetail = $this->commonFunctionRepo->fileGetContentAdtopia($adtopiapixel, 'adtopia_' . $pixel_type . '_curl_info', 'post', $arrUrlParams);
            $arrResultDetail['adtopiapixel'] = $adtopiapixel;
        }
        //if tracker = 1
        if ($tracker_type == '1') {
            if ($pixel_type == 'LP') {
                $vpFiringObj = new FollowupVendorPixelFiring();
                $vpFiringObj->visitor_id = $intVisitorId;
                $vpFiringObj->vendor = 'adtopia';
                $vpFiringObj->page_type = 'LP';
                $vpFiringObj->pixel_type = 'web';
                $vpFiringObj->pixel_log = $arrResultDetail['result_detail'];
                $vpFiringObj->followup_visit_id = $flvvisit_id;
                $vpFiringObj->created_at         = @$currentTime;
                $vpFiringObj->save();
            } else {
                $vpFiringObj = new FollowupVendorPixelFiring();
                $vpFiringObj->visitor_id = $intVisitorId;
                $vpFiringObj->user_id = $intUserId;
                $vpFiringObj->vendor = 'adtopia';
                $vpFiringObj->page_type = $pixel_type;
                $vpFiringObj->pixel_type = 'web';
                $vpFiringObj->pixel_log = $arrResultDetail['result_detail'];
                $vpFiringObj->followup_visit_id = $flvvisit_id;
                $vpFiringObj->created_at         = @$currentTime;
                $vpFiringObj->save();
            }
        }
        return $arrResultDetail;
    }
}
