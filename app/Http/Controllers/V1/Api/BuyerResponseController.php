<?php

namespace App\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\BuyerApiResponse;
use App\Models\ApiHistory;
use App\Repositories\Interfaces\LogInterface;

class BuyerResponseController extends Controller
{
    /**
     * BuyerResponseController constructor.
     *
     * @param LogInterface $logRepo
     */
    public function __construct(LogInterface $logRepo){
        $this->logInterface = $logRepo;
    }

    /**
     * Index
     *
     * @param Request $request
     * @return bool|string
     */
    public function index(Request $request)
    {
        $url            =   (isset($request->PostURL) ? $request->PostURL : '');
        $token          =   (isset($request->Authorization) ? $request->Authorization : '');
        $UserAgent      =   (isset($request->UserAgent) ? $request->UserAgent : '');
        $PostBody       =   (isset($request->PostBody) ? $request->PostBody : '');
        $requestArr     = array(
                            'PostURL'=>$url,
                            'Authorization'=>$token,
                            'UserAgent'=>$UserAgent,
                            'PostBody'=>$PostBody
                            );
        $headers        =   array(
                                    "Content-type: application/json",
                                    "Cache-Control: no-cache",
                                    "Authorization: Bearer $token",
                                    "User-Agent: $UserAgent",
                            );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $PostBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $strFileContent = "\n\n Middleman --- REQUEST: " . serialize(@$requestArr)."\n";
        $strFileContent .= "\n\n Response: " . serialize($response)."\n";
        $logWrite = $this->logInterface->writeLog('-middleman', $strFileContent);
        return $response;
    }
}
