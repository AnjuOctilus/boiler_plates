<?php 

namespace App\Repositories\Interfaces;

interface HistoryInterface
{
	public function insertFollowupHistory($request);
	public function insertFollowupLiveHistory($request);
	public function arrayWalkBasic($value, $key, $extraParam);
	public function insertFollowupBasicHistory($request);
	public function createApiHistory($request);
	public function userCRMHistory($userId);
	public function userFollowHistory($userId);
}