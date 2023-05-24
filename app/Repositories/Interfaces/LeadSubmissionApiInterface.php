<?php

namespace App\Repositories\Interfaces;

interface LeadSubmissionApiInterface
{
	public function commonSplits($data, $currentTime);
	public function store($data, $data_query, $params, $currentTime, $pageName, $visitorData);
	public function savePhoneDetails($userId,$data, $currentTime);
	public function storeLeadData($data,$uuid);
	public function saveUserBanks($banks,$userId);
	public function storeQuestion($data,$uuid);
	public function saveUserBanksAPI($banks,$userId,$isJoint);
	public function storeUserAddressDetails($userId,$data);
}
