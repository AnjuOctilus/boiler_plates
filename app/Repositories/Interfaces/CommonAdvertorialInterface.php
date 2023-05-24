<?php 

namespace App\Repositories\Interfaces;

interface CommonAdvertorialInterface
{
   public function initAdvertorial($request);
	public function redirectUrl($request,$inADVvisitorId,$strFileName,$visitorParams,$full_url="",$domain="");
}	