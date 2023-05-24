<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AdvDataIngestionInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\ApiClassInterface;
use App\Repositories\Interfaces\UAInterface;
use App\Repositories\Interfaces\UserInterface;
use App\Repositories\Interfaces\LPDataIngestionInterface;
use App\Repositories\Interfaces\DataIngestionInterface;
use App\Repositories\CommonFunctionsRepository;
/**
 * Class UserAgentController
 *
 * @package App\Http\Controllers\V1
 */
class UserAgentController extends Controller
{
    /**
     * UserAgentController constructor.
     *
     * @param ApiClassInterface $api_repo
     * @param UAInterface $ua_repo
     * @param UserInterface $user_repo
     * @param LPDataIngestionInterface $lp_data_ingestion_repo
     * @param DataIngestionInterface $data_ingestion_repo
     * @param AdvDataIngestionInterface $advDataIngestionInterface
     */
    public function __construct(ApiClassInterface $api_repo, UAInterface $ua_repo, UserInterface $user_repo, LPDataIngestionInterface $lp_data_ingestion_repo, DataIngestionInterface $data_ingestion_repo, AdvDataIngestionInterface $advDataIngestionInterface)
    {
        $this->api_repo = $api_repo;
        $this->ua_repo = $ua_repo;
        $this->user_repo = $user_repo;
        $this->lp_data_ingestion_repo = $lp_data_ingestion_repo;
        $this->data_ingestion_repo = $data_ingestion_repo;
        $this->advDataIngestionInterface = $advDataIngestionInterface;
    }
    /**
     *
     * Get user agent info
     */
    public function getUseragentInfo(Request $request)
    {
        $data = $request->all();
        $valid = $this->api_repo->validateToken($request);
        if ($valid == 1) {

            if ($request->page_type == 'LP') {
                $data_ingestion_arr['lp_param'] = $this->lp_data_ingestion_repo->setLPParam($request);
                $arrUserInfo = ['data' => $data_ingestion_arr['lp_param']];
                $dataResponse = ['response' => $arrUserInfo, 'status' => 'Success'];
            }
            else if ($request->page_type == 'AP') {
                $data_ingestion_arr['adv_param'] = $this->advDataIngestionInterface->setAgentVisitorParam($request);
                $data_ingestion_arr['adv_param'] = $data_ingestion_arr['adv_param'];
                $arrUserAgentInfo = ['data' => $data_ingestion_arr['adv_param']];
                $dataResponse = ['response' => $arrUserAgentInfo, 'status' => 'Success'];
            } else {
                $dataResponse = array('response' => 'Unknown page_type', 'status' => 'Failed');
            }
        } else {
            $dataResponse = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }
    /**
     * Get uuid
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUUID(Request $request)
    {
        $data   = $request->all();
        $valid              = $this->api_repo->validateToken($request);
        if ($valid == 1) {
            $uuid = (isset($request->uuid) && $request->uuid != null) ? $request->uuid : $this->user_repo->GenerateUuid();
            $common_fn    =   new CommonFunctionsRepository();
            $strIp        =   $common_fn->get_client_ip();
            $arrUserInfo  = ['uuid' => $uuid, 'ip_address' => $strIp, 'status' => 'Success'];
            $dataResponse =  ['response' => $arrUserInfo];
        } else {
            $dataResponse  = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }
}
