<?php

namespace App\Repositories;

use App\Models\SiteConfig;
use App\Models\QueueHaltTable;
use App\Mail\ApiErrorMail;
use App\Repositories\Interfaces\QueueInterface;
use Illuminate\Support\Facades\Mail;

/**
 * Class QueueRepository
 *
 * @package App\Repositories
 */
class QueueRepository implements QueueInterface
{
    /**
     * Queue status fetch
     *
     * @return array|string[]
     */
    public function queueStatusFetch()
    {
        $status_query = SiteConfig::where('config_title', 'QUEUE_STATUS');
        $queue_status = $status_query->pluck('config_value');
        $queue_status = $queue_status[0];
        $queue_fail_count = $status_query->pluck('config_info');
        $queue_fail_count = $queue_fail_count[0];
        if ($queue_status == "TRUE" || $queue_status == "FALSE") {
            $p = array("status" => $queue_status, "count" => $queue_fail_count);

            return array("status" => $queue_status, "count" => $queue_fail_count);
        } else {
            return array("status" => "failed");
        }
    }

    /**
     * Queue fail call
     *
     * @return array|string[]
     */
    public function queueFailCall()
    {
        $status_query = SiteConfig::where('config_title', 'QUEUE_STATUS');
        $queue_status = $status_query->pluck('config_value');
        $queue_status = $queue_status[0];
        $queue_fail_count = $status_query->pluck('config_info');
        $queue_fail_count = $queue_fail_count[0];
        $queue_fail_limit = SiteConfig::where('config_title', 'QUEUE_FAIL_COUNT');
        $fail_counter_val = $queue_fail_limit->pluck('config_value');
        $fail_counter_val = $fail_counter_val[0];
        if ($queue_status == "TRUE" && $queue_fail_count == $fail_counter_val - 1) {
            SiteConfig::where('config_title', 'QUEUE_STATUS')->update(["config_info" => $fail_counter_val, "config_value" => "FALSE"]);
            $error_type = 'OPC Queues Stopped due to ' . $fail_counter_val . ' errors';
            $this->callAPIErrorMail($error_type);

            return $this->queueStatusFetch();
        } else {
            $queue_fail_count++;
            SiteConfig::where('config_title', 'QUEUE_STATUS')->update(["config_info" => $queue_fail_count]);

            return $this->queueStatusFetch();
        }
    }

    /**
     * Queue fail false call
     *
     * @return array|string[]
     */
    public function queueFailFalseCall()
    {
        $status_query = SiteConfig::where('config_title', 'QUEUE_STATUS');
        $queue_status = $status_query->pluck('config_value');
        $queue_status = $queue_status[0];
        $queue_fail_count = $status_query->pluck('config_info');
        $queue_fail_count = $queue_fail_count[0];
        if ($queue_fail_count >= 1) {
            $queue_fail_count--;
            SiteConfig::where('config_title', 'QUEUE_STATUS')->update(["config_info" => $queue_fail_count]);
            return $this->queueStatusFetch();
        }
    }

    /**
     * Queue fail scenario
     *
     * @param string $user_id
     * @param string $visitor_id
     * @return string[]
     */
    public function queueFailScenario($user_id = '', $visitor_id = '')
    {
        if ($user_id != '' && $visitor_id != '') {
            QueueHaltTable::create(['user_id' => $user_id, 'visitor_id' => $visitor_id]);
            return array("status" => "success");
        } else {
            return array("status" => "failed");
        }
    }

    /**
     * Queue halt table complete
     *
     * @param string $user_id
     */
    public function queueHaltTableComplete($user_id = '')
    {
        if ($user_id != '') {
            QueueHaltTable::where('user_id', $user_id)->update(["complete" => 1]);
        }
    }

    /**
     * Queue status reset
     *
     * @return string[]
     */
    public function queueStatusReset()
    {
        $status_query = SiteConfig::where('config_title', 'QUEUE_STATUS')
            ->update([
                "config_value" => "TRUE",
                "config_info" => 0
            ]);

        return array("status" => "success");
    }

    /**
     * Call Api error mail
     *
     * @param $request_type
     */
    public function callAPIErrorMail($request_type)
    {
        // mailing when api fail
        $env_branch = env('APP_ENV');
        if ($env_branch == 'local' || $env_branch == 'dev') {
            $strTo = config('constants.TO_EMAIL_API_TEST');
        } else if ($env_branch == 'pre' || $env_branch == 'live') {
            $strTo = config('constants.TO_EMAIL_API_ERROR');
        }
        $message = 'API ' . $request_type . ' failed';
        $email = $strTo;
        $strSubject = "Online Car Finance Claims : Web-Service Failure - " . $message;
        $strFrom = "developers@vandalayglobal.com";
        $result = SELF::fnMailgunGeneralMail($strSubject, "<p>Hello, </p><p><b>Alert</b> For Email Address:" . $email . ", </p><p> Branch :" . $env_branch . "</p><p> Message : <b>" . $message . "</b></p><p>Thankyou</p>", $strTo, $strFrom, $message);
    }

    /**
     * Fnmail gun general mail
     *
     * @param $strSubject
     * @param $strContent
     * @param $strTo
     * @param $strFrom
     * @param $message
     * @return string
     */
    public static function fnMailgunGeneralMail($strSubject, $strContent, $strTo, $strFrom, $message)
    {
        $stFromName = 'Online Car Finance Claims';
        $arrPostFields = array('from' => $stFromName . " <" . $strFrom . ">",
            'to' => $strTo,
            'subject' => $strSubject,
            'html' => $strContent
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:key-d9f5aa2a55343d8c135e35560001ea84');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v2/simplypmi.co.uk/messages');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrPostFields);
        $result = curl_exec($ch);
        curl_close($ch);

        $arrResult = (array)json_decode($result);
        $strResult = 'Error';
        if (isset($arrResult['message'])) {
            $strResult = ((preg_match('/Thank you/i', $arrResult['message'])) ? 'Success' : 'Error');
        }
        return $strResult;
    }

}
