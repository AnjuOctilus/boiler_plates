<?php


namespace App\Repositories\Interfaces;


Interface FollowupSmsEmailEndPointInterface
{

  public function SendEmail($arrParamData);

  public function SendSms($arrParamData);

  public function sendStaticEmail($userId,$content);
  public function sendStaticSMS($userId,$content);

}