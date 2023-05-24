<?php


namespace App\Repositories\Interfaces;


interface FollowupDataIngestionInterface
{
	
public function saveFollowUpData($data,$queryString,$currentTime);
public function signatureStore($signatureDate,$currentTime,$followupData);
public function questionStore($questionData,$followupData);
}