<?php


namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\FollowupSmsEmailEndPointInterface;
use DB;

use Illuminate\Http\Request;


class FollowupEmailSmsSingleEndPointController extends Controller
{
    public function __construct(FollowupSmsEmailEndPointInterface $smsEmailSingleRepo)
    {
        $this->smsEmailFollowup   = $smsEmailSingleRepo;
    }

    public function TestSmsFollowupSingleEndPoint($user_id,$template_id)
    {
        $this->smsEmailFollowup->SendSms(array('user_id' =>$user_id,'template_id' =>$template_id,'domain_id'=>1,'short_url'=>'https://tms.onl','followup_stage'=>'s1'));
        return "Success";
    }
    public function TestEmailFollowupSingleEndPoint($user_id,$template_id)
    {
     
        $this->smsEmailFollowup->SendEmail(array('user_id' =>$user_id,'template_id' =>$template_id,'domain_id'=>1,'short_url'=>'https://tms.onl','followup_stage'=>'E1'));
        return "Success";
    }
    
}
