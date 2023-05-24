<?php

namespace App\Repositories;

use App\Repositories\Interfaces\EmailInterface;
use Config;

/**
 * Class EmailRepository
 *
 * @package App\Repositories
 */
class EmailRepository implements EmailInterface
{

    /**
     * Fnsend general mail  AWS
     *
     * @param $strSubject
     * @param $strFileContents
     * @param null $strSendTo
     * @param array $arrSendCC
     */
    public function fnSendGeneralMailAWS($strSubject, $strFileContents, $strSendTo = NULL, $arrSendCC = array())
    {
        if (is_null($strSendTo)) {
            $strSendTo = Config::get('constants.TO_EMAIL_ADDRESS');
        }
        $strFromEmail = Config::get('constants.FROM_EMAIL_ADDRESS');
        $strSubject = Config::get('constants.SITE_NAME') . ":" . $strSubject;
        SELF::fnMailgunGeneralMail($strSubject, $strFileContents, $strSendTo, $strFromEmail);
    }

    /**
     * FnMailgun general mail
     *
     * @param $strSubject
     * @param $strContent
     * @param $strTo
     * @param $strFrom
     * @return string
     */
    public function fnMailgunGeneralMail($strSubject, $strContent, $strTo, $strFrom)
    {
        $stFromName = Config::get('constants.FROM_EMAIL_NAME');
        $arrPostFields = array(
            'from' => $stFromName . " <" . $strFrom . ">",
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
