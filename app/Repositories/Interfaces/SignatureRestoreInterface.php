<?php 

namespace App\Repositories\Interfaces;

interface SignatureRestoreInterface
{
    public function signatureStore($data);
    public function updateFollowUpCompleteStatus($userId,$source);
    public function getSignData($userId);
    public function getQuestionData($userId);


}