<?php

namespace App\Repositories\Interfaces;

interface LiveSessionInterface
{
	public function insertBasicLiveSession($request);
	public function createUserMilestoneStats($request);
	public function createPartnerMilestoneStats($request);
	public function completedStatusUpdate($userId, $source = 'live');
}
