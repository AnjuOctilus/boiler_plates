<?php 

namespace App\Repositories\Interfaces;

interface EmailInterface
{
public function fnSendGeneralMailAWS($strSubject, $strFileContents, $strSendTo = NULL, $arrSendCC = array());
public function fnMailgunGeneralMail($strSubject, $strContent, $strTo, $strFrom);
}