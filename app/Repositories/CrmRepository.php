<?php


namespace App\Repositories;


use App\Models\BuyerApiResponse;
use App\Models\BuyerApiResponseDetails;
use App\Models\User;
use App\Repositories\Interfaces\CRMInterface;
use Illuminate\Support\Facades\DB;
use App\Repositories\LogRepository;
use App\Models\UserAddressDetails;
/**
 * Class CrmRepository
 *
 * @package App\Repositories
 */
class CrmRepository implements CRMInterface
{
    /**
     * CrmRepository constructor.
     */
    public function __construct()
    {
        $this->logRepo = new LogRepository;
    }

    /**
     * Crm posting
     *
     * @param $userId
     */
    public function crmPosting($userId)
    {
        $userData = $this->getUserData($userId);
        if ($userData) {
            if (!empty($userData->questionnaire)) {
                $questionData = [];
                $rowData = explode(",", $userData->questionnaire);
                foreach ($rowData as $row1 => $tr) {
                    $row = explode(" ", $tr);
                    $question_ans = array('question_map_id' => $row['0'],
                        'option_map_id' => $row['1'],
                        'option_value' => $row['2'],
                        'input_text' => ''

                    );
                    array_push($questionData, $question_ans);
                }
            }
            $data = $this->formatPostData($userData, $questionData);
            $result = $this->processCrmPosting($data, $userId,);
        }
    }
    /**
     * Format post data
     *
     * @param $userData
     * @param $questionData
     * @return array
     */
    public function formatPostData($userData, $questionData)
    {
        $isEnrolled = 'No';
        $isReceivingBenefit = 'No';
        if (sizeof($questionData) > 0) {
            foreach ($questionData as $each) {
                if ($each['question_map_id'] == 1 && $each['option_map_id'] == 1) {
                    $isEnrolled = 'Yes';
                }
                if ($each['question_map_id'] == 2 && $each['option_map_id'] == 3) {
                    $isReceivingBenefit = 'Yes';
                }
            }
        }
        $postcode_data = UserAddressDetails::where(['postcode' => $userData->postcode])->first();
        $data = array(
            'user_id' => $userData->user_id,
            'first_name' => $userData->first_name,
            'last_name' => $userData->last_name,
            'dob' => $userData->dob,
            'email' => $userData->email,
            'phone_number' => $userData->telephone,
            'zipcode' => $userData->postcode,
            'optin_date' => $userData->created_at->format('Y-m-d H:i:s'),
            'is_enrolled' => $isEnrolled,
            'is_receiving_benefits' => $isReceivingBenefit,
            'is_test' => ($userData->record_status == 'TEST') ? 0 : 1,
            'user_token' => $userData->token,
            'domain_name' => $userData->domain_name,
            'split_name' => $userData->split_name,
            'ip_address' => $userData->ip_address,
            'media_vendor' => @$userData->media_vendor,
            'adtopia_vendor' => @$userData->atp_vendor,
            'adtopia_source' => @$userData->atp_source,
            'device' => $userData->device_site_name,
            'device_type' => $userData->device_type,
        );
        return $data;
    }
    /**
     * Get user data
     *
     * @param $userId
     * @return mixed
     */
    public function getUserData($userId)
    {
        $data = User::where(['users.id' => $userId])
            ->select(
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'users.dob',
                'users.email',
                'users.telephone',
                'users.token',
                'users.record_status',
                'users.created_at',
                'ued.postcode',
                'si.split_name',
                'dd.domain_name',
                'vi.ip_address',
                'av.atp_source',
                'av.atp_vendor',
                'av.media_vendor',
                'vi.device_type',
                'dsm.device_site_name',
                DB::raw('GROUP_CONCAT(Q.default_id, " ",QO.default_id, " ",QO.value SEPARATOR " ,") as  questionnaire')
            )
            ->leftJoin('user_extra_details as ued', 'users.id', '=', 'ued.user_id')
            ->leftJoin('visitors as vi', 'users.visitor_id', '=', 'vi.id')
            ->leftJoin('split_info as si', 'vi.split_id', '=', 'si.id')
            ->leftJoin('domain_details as dd', 'si.domain_id', '=', 'dd.id')
            ->leftJoin('adtopia_visitors as av', 'vi.id', '=', 'av.visitor_id')
            ->leftJoin('device_site_masters as dsm', 'vi.device_site_id', '=', 'dsm.id')
            ->leftJoin('user_questionnaire_answers as UQA', 'UQA.user_id', '=', 'users.id')
            ->leftJoin('questionnaires as Q', 'Q.id', '=', 'UQA.questionnaire_id')
            ->leftJoin('questionnaire_options as QO', 'QO.id', '=', 'UQA.questionnaire_option_id')
            ->groupBy('users.id')
            ->first();
        return $data;
    }

    /**
     * Process crm posting
     *
     * @param $data
     * @param $userId
     */
    public function processCrmPosting($data, $userId)
    {
        $buyerResponse = BuyerApiResponse::where(['user_id' => $userId, 'result' => 'Success', 'buyer_id' => 2])->get()->toArray();
        if (sizeof($buyerResponse) < 1) {
            $crm_url = env('CRM_URL');
            $url = $crm_url . "/api/medi/v1/operations?op=lead-insert";
            $response = $this->sendToCrm($url, $data);
            $result = json_decode($response);
            $request = json_encode($data);
            $api_histories = array(
                'user_id' => $userId,
                'url' => $url,
                'request' => $request,
                'response' => $response,
            );
            DB::table('api_histories_new')->insertGetId($api_histories);
        }
        if (isset($result->status) && ($result->status == 'Success')) {
            $buyer_data = array(
                'buyer_id' => '2',
                'user_id' => $userId,
                'result' => $result->status,
                'lead_id' => isset($result->crm_lead_id) ? $result->crm_lead_id : 'NULL',
                'api_response' => $response,
            );
            $buyerResponseId = DB::table('buyer_api_responses')->insertGetId($buyer_data);
            $dataDetail = array(
                'buyer_api_response_id' => $buyerResponseId,
                'post_param' => $request
            );
            DB::table('buyer_api_response_details')->insertGetId($dataDetail);
            DB::table('followup_histories')->where('user_id', $userId)
                ->update([
                    'post_crm' => 1
                ]);
            $FileContent_new = "--Data Send-" . json_encode($data) . "-- user_id: " . $userId . "-- Response: " . json_encode($result) . "-- Lead ID: " . "--------------\n";

            $logWrite = $this->logRepo->writeLog('-MEDICARELeadsTocrm', $FileContent_new);
        }
    }
    /**
     * Send to crm
     *
     * @param $url
     * @param $postData
     * @return bool|string
     */
    public function sendToCrm($url, $postData)
    {
        if (env('APP_ENV') == 'local') {
            $response = array("response" => "lead added successfully",
                "status" => "Success",
                'crm_lead_id' => 125);
            return json_encode($response);
        }
        $fields_string = http_build_query($postData);

        $CRM_TOKEN = env('CRM_AUTH_TOKEN');
        $header = [
            'Accept: application/json',
            'Cache-Control: no-cache',
            'Authorization: Bearer ' . $CRM_TOKEN,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
