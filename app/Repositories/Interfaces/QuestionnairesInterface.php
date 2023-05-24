<?php

namespace App\Repositories\Interfaces;

interface QuestionnairesInterface
{
public function isQuestionnaireComplete($userId);
public function isPartnerQuestionnaireComplete($userId);
public function isQuestionnairePageComplete ($userId);
public function createUserQuestionnaireStats($request);
public function getQuestionnaireVersion();
public function getRandom($url_array,$numerical_array);
public function createquestionnaireMeta($request);
public function checkQuestionnaireVersion($userId);
public function getPendingQuestions($user_id);
public function getFollowUpPendingQuestions($user_id);
public function getFollowUpPendingQuestionsCount($user_id);
public function getFollowUpFilledUpQuestions($user_id);
public function getFollowUpQuestionsAnswers($user_id);
public function saveQuestionaires($visitorParameters,$questionData,$formData,$visitorData,$queryString);
}
