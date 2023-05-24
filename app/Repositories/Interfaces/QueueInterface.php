<?php 

namespace App\Repositories\Interfaces;

interface QueueInterface
{
    public function queueStatusFetch();
    public function queueFailCall();
    public function queueFailFalseCall();
    public function queueFailScenario($user_id = '', $visitor_id = '');
    public function queueHaltTableComplete($user_id = '');
    public function queueStatusReset();
    public function callAPIErrorMail($error_type);
}