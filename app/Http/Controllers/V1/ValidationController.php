<?php


namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ValidationInterface;
use App\Repositories\EmailRepository;
use App\Repositories\LogRepository;
use Illuminate\Http\Request;

/**
 * Class ValidationController
 *
 * @package App\Http\Controllers\V1
 */
class ValidationController extends Controller
{
    /**
     * Get valid phone
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function __construct(ValidationInterface $validation_repo)
    {
        $this->validation_repo = $validation_repo;
    }

    /*public function getValidPhone(Request $request)
    {
        $strTelephone    =   $request->phone;
        $intVisitorId     =   $request->visitor_id;
        $strTelephone    =   preg_replace('/[^0-9]/', '', $strTelephone);
        if (isset($request->product)) {
            $product = $request->product;
        } else {
            $product =  null;
        }
        if ($strTelephone) {
            // $str_tel_result = $this->validation_repo->CheckValidPhoneNumberApi($strTelephone, $intVisitorId);
            $dataArray = ['status_code' => 1];
         } else {
             //   $dataArray = ['status' => 'Fail','message' => 'Phone number is missing'];
             $dataArray = ['status_code' => 3];
         }
        /*if ($strTelephone) {
            $str_tel_result = $this->validation_repo->CheckValidPhoneNumberApi($strTelephone, $intVisitorId);
            if (!preg_match('/success/i', $str_tel_result)) {
                $return =  0;
                $dataArray = ['status_code' => $return];
            } else {
                $today_date = date('Y-m-d');

                if ($this->validation_repo->checkPhoneDuplicate($strTelephone)) {
                    $return =  2;
                } else {
                    $return =  1;
                }
                $dataArray = ['status_code' => $return];
            }
        } else {
            //   $dataArray = ['status' => 'Fail','message' => 'Phone number is missing'];
            $dataArray = ['status_code' => 3];
        }
        return response()->json($dataArray);
    }*/

    public function getValidPhone(Request $request)
    {
        if (env('APP_ENV') == 'local') {
            return response()->json(['status_code' => 1]);
        }

        $strTelephone    =   $request->phone;
        $intVisitorId     =   $request->visitor_id;
        $strTelephone    =   preg_replace('/[^0-9]/', '', $strTelephone);
        if (isset($request->product)) {
            $product = $request->product;
        } else {
            $product =  null;
        }

        if ($strTelephone) {
            $str_tel_result = $this->validation_repo->CheckValidPhoneNumberApi($strTelephone, $intVisitorId);
            if (!preg_match('/success/i', $str_tel_result)) {
                $return =  0;
                $dataArray = ['status_code' => $return];
            } else {
                $today_date = date('Y-m-d');

                if ($this->validation_repo->checkPhoneDuplicate($strTelephone)) {
                    $return =  2;
                } else {
                    $return =  1;
                }
                $dataArray = ['status_code' => $return];
            }
        } else {
            //   $dataArray = ['status' => 'Fail','message' => 'Phone number is missing'];
            $dataArray = ['status_code' => 3];
        }
        return response()->json($dataArray);
    }
    /**
     * Get valid email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getValidEmail(Request $request)
    {
        $strEmail = $request->email;
        $intVisitorId = $request->visitor_id;
        if (isset($request->product)) {
            $product = $request->product;
        } else {
            $product = null;
        }
        $strEmail = strtoupper(str_replace(' ', '', $strEmail));

        if ($strEmail) {
            $strPostalResult = $this->validation_repo->CheckValidEmail($strEmail, $intVisitorId, $product);
            if (!$strPostalResult) {
                $return = 1;
            } else {
                $return = 0;
            }
            $dataArray = ['status_code' => $return];
        } else {
            $dataArray = ['status' => 'Fail', 'message' => 'Email is missing'];
        }
        return response()->json($dataArray);
    }
    /**
     * Get valid zip
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
}
