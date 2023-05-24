<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\ApiClassInterface;
use App\Repositories\Interfaces\HistoryInterface;
use GuzzleHttp\Client;

class ContactController extends Controller
{

    public function __construct(ApiClassInterface $apiClassInterface, HistoryInterface $historyInterface)
    {
        $this->apiClassInterface = $apiClassInterface;
        $this->historyInterface = $historyInterface;
    }


    public function index(Request $request)
    {

        $valid  = $this->apiClassInterface->validateToken($request);
        if ($valid == 1) {
            $phonenumber    =   $request->phone;
            $email          =   $request->email;
            $message        =   $request->message;
            $strFrom        =   $email;
            $strTo          =   "lindsay@yourlondonbridge.com";
            $result         =   SELF::sendEmail($strTo, $strFrom, $email, $phonenumber, $message);
            $dataResponse   =   ['status' => 'Success'];
            $historyRequest =   array(
                                    'user_id' => null,
                                    'url' => 'v1\Api\adv_contact',
                                    'request' => $request->all(),
                                    'response' => $dataResponse
                                );
            $this->historyInterface->createApiHistory($historyRequest);
        } else {
            $dataResponse   = array('response' => 'Authentication Failed', 'status' => 'Failed');
        }
        return response()->json($dataResponse);
    }

    public function sendEmail($strTo, $strFrom, $email, $phonenumber, $message)
    {
        $stFromName         = 'senior-benefits';
        $mailer_endpoint    = null;
        switch (env('APP_ENV')) {
            case 'live':
                $mailer_endpoint = config('constants.MAILER_ENDPOINT');
                break;
            case 'pre':
                $mailer_endpoint = config('constants.MAILER_ENDPOINT_PRE');
                break;
            default:
                $mailer_endpoint = config('constants.MAILER_ENDPOINT_DEV');
                break;
        }

        $retResponse = [];
        try {
            $client     = new Client();
            $response   = $client->request('POST', $mailer_endpoint, [
                'headers'   => ['Authorization' => 'Bearer ' . env('ADTOPIA_TOKEN')],
                'json'      => [
                    "ProjectCode"   => env('ADTOPIA_UPID'),
                    "Environment"   => strtoupper(env('APP_ENV')),
                    "EmailDetails"  => [
                        "From"      => [
                            "Name"      => $stFromName,
                            "Email"     => $strFrom,
                        ],
                        "To"        => [
                            0 => [
                                "Name"      => $strTo,
                                "Email"     => (env('APP_ENV') == 'live') ? $strTo : 'sreekuttan.ps@vandalayglobal.com',
                            ]
                        ],
                        "Subject"       =>  "Quotes Capital Contact Form Details",
                        "Body"          =>  "<p>Email : " . $email .
                            "<br>PhoneNumber : " . $phonenumber .
                            "<br>Message : " . $message . "</p>",
                        "Attachments"   =>  "",
                    ],
                ],
            ]);

            $retResponse['status']      = 'success';
            $retResponse['response']    = $response->getBody()->getContents();
        } catch (\Exception $e) {
            $retResponse['status']      = 'failed';
            $retResponse['response']    = $e->getMessage();
        }

        return $retResponse;
    }
}
