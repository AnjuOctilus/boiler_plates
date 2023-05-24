<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\CommonSplitsInterface;
use App\Repositories\Interfaces\ApiClassInterface;

/**
 * Class SplitDataController
 *
 * @package App\Http\Controllers\V1
 */
class SplitDataController extends Controller
{
    /**
     * SplitDataController constructor.
     *
     * @param ApiClassInterface $api_repo
     * @param CommonSplitsInterface $common_split_repo
     */
    public function __construct(ApiClassInterface $api_repo, CommonSplitsInterface $common_split_repo)
    {
        $this->api_repo = $api_repo;
        $this->common_split_repo = $common_split_repo;
    }

    /**
     * Get bank list
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBankList(Request $request)
    {
        $data = $request->all();
        $valid = $this->api_repo->validateToken($request);
        if ($valid == 1) {
            $data['banks'] = $this->common_split_repo->getBanks(1);
            $data['other_banks'] = $this->common_split_repo->getBanks(2);
            $dataResponse = ['response' => $data, 'status' => 'Success'];

        } else {
            $dataResponse = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }
}
