<?php

namespace App\Repositories;

use \Illuminate\Support\Facades\Validator;
use App\Repositories\Interfaces\ApiClassInterface;
use App\Models\UserVehicleValuationList;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Class ApiClassRepository
 *
 * @package App\Repositories
 */
class ApiClassRepository implements ApiClassInterface
{
    /**
     * Validate token
     *
     * @param $request
     * @return int
     */
    public static function validateToken($request)
    {
       
        $tokenget = $request->header('Authorization');
        if (strpos($tokenget, ' ') > -1) {
            list($b, $t) = @explode(' ', $tokenget);
        } else {
            $b = $tokenget;
        }
        $token = str_replace("Bearer ", "", $tokenget);
        $actualToken = config('constants.AUTH_API_TOKEN');
        if ($actualToken == $token) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Validate request
     *
     * @param $request
     * @return int|mixed
     */
    public static function validateRequest($request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'source' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $item) {
                return $item[0];
            }
        } else {
            return 1;
        }

    }

    /**
     * Valudation list
     *
     * @param $regNo
     * @param $userId
     * @return string
     */
    public function valuationList($regNo, $userId)
    {
        $auth_apikey_test = config('constants.VEHICLE_VALUATION_DATA_KEY_TEST');
        $auth_apikey_live = config('constants.VEHICLE_VALUATION_DATA_KEY_LIVE');

        try {
            if (env('APP_ENV') == 'live' || env('APP_ENV') == 'pre') {
                $vehicle_api = 'https://uk1.ukvehicledata.co.uk/api/datapackage/ValuationData?v=2&api_nullitems=1&auth_apikey=' . $auth_apikey_live . '&key_VRM=' . $regNo;

            } else {
                $vehicle_api = 'https://uk1.ukvehicledata.co.uk/api/datapackage/ValuationData?v=2&api_nullitems=1&auth_apikey=' . $auth_apikey_test . '&key_VRM=' . $regNo;
            }
            $data = file_get_contents($vehicle_api);
            $data_Response = json_decode($data, true);
            $valuation_list = $data_Response['Response']['DataItems'];
            $valuation_details = $valuation_list['ValuationList'];
            $valuation_array = array('user_id' => $userId,
                'otr' => $valuation_details['OTR'],
                'car_reg_no' => $regNo,
                'dealer_forecourt' => $valuation_details['DealerForecourt'],
                'trade_retail' => $valuation_details['TradeRetail'],
                'private_clean' => $valuation_details['PrivateClean'],
                'private_average' => $valuation_details['PrivateAverage'],
                'part_exchange' => $valuation_details['PartExchange'],
                'auction' => $valuation_details['Auction'],
                'trade_average' => $valuation_details['TradeAverage'],
                'trade_poor' => $valuation_details['TradePoor'],
            );
            $user_exist = UserVehicleValuationList::where('user_id', $userId)->first();
            if (!$user_exist) {
                UserVehicleValuationList::insert($valuation_array);

            }
            $result = 'success';

        } catch (\Exception $exception) {
            $msg = " failed . user id :" . $userId . " ... ERROR MESSAGE :" . $exception->getMessage();
            Log::warning($msg);
            $result = 'failed';
        }
        return $result;
    }
}
