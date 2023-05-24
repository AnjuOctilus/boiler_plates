<?php

namespace App\Repositories\Interfaces;

interface UserInterface
{
	public function updateUserTimestamp($userId);
	public function isUserComplete($userId);
	public function isPdfDocComplete($userId);
	public function isQualified($userId);
	public function getVisitorUserTransDetails($intVisitorId, $intUserId, $sqlField);
	public function insertIntoUser($intVisitorId, $arrData,$currentTime);
	public function insertBuyerApiResponse($intUserId, $arrData);
	public function insertBuyerApiResponseDetails($buyer_api_response_id,$arrData);
	public function getLeadId($intUserId);
	public function storeUser($request,$recordStatus,$currentTime,$domain_name );
	public function storeHistory($intUserId);
	public function userDetails($userId);
	public function userQuestionnaireAnswers($userId);
	public static function GenerateUuid();
	public function isPdfDocCompleteBank($userId);
	public function isUserQualified($userId);
	public function storeQuestionsHistory($userId);
	public function getUserData($token);
	public function isUserSignComplete($userId);
	public function getFollowUpUserDetailsS1();
	public function updateUserExtraCompleteStatus($userId);
	public function getFollowUpUserDetailsS2();
	public function getFollowUpUserDetailsS3();
	public function getUserBankDetails($userId);
}
