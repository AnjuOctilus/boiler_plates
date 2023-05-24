<?php

namespace App\Repositories\Interfaces;

interface CakeInterface
{
	public function cakePost($userId, $buyyerId);
	public function sendUserInfoToCake($userId,$recordStatus, $milestoneStatus);
}
