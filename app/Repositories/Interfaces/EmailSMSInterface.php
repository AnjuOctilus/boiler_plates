<?php 

namespace App\Repositories\Interfaces;

interface EmailSMSInterface
{
	public function getFollowUpUserDetails($smsStatus, $emailStatus=NULL);
	public function getFollowUpEmailUserDetails($status,$start, $end);
	
}