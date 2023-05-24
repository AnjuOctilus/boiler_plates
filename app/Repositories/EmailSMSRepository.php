<?php

namespace App\Repositories;

use App\Models\DeviceSiteMaster;
use App\Repositories\CommonFunctionsRepository;
use App\Repositories\BrowserDetectionRepository;
use App\Repositories\MobileDetectRepository;
use App\Repositories\Interfaces\EmailSMSInterface;

/**
 * Class UARepository
 *
 * @package App\Repositories
 */
class EmailSMSRepository implements EmailSMSInterface
{
    public $useragent;

    /**
     * UARepository constructor.
     */
    public function __construct()
    {
        $this->commonFunctionRepo = new CommonFunctionsRepository();
        $this->browserDetection = new BrowserDetectionRepository();
        $this->mobileDetect = new MobileDetectRepository();
    }

     /**
             * Return userdata that has already no entry  followup_sategs table with stage e2
             */
            public function getFollowUpEmailUserDetails($status,$start, $end){
                $e2data = \Illuminate\Support\Facades\DB::table('followup_stages AS a')
                ->join('user_extra_details AS u','a.user_id','=','u.user_id')
                ->join('users AS user','a.user_id', '=', 'user.id')
                ->where('user.created_at', '>=', Carbon::now()->subHour(26))
                ->where('user.created_at', '<=', Carbon::now()->subHour(24))
                ->where('u.complete_status', '=', 0)
                ->where('a.stage', '=', $status)  
                ->select('a.user_id AS id')
                ->whereNotExists(function($query)
                {
                $query->select(DB::raw(1))
                ->from('followup_stages AS b')
                ->whereRaw('a.user_id = b.user_id ')
                ->Where('b.stage', '=', 'e2');
                })
                ->get()->toArray();

                return $e2data;
      

            }
    
    /**
     * Return userdata that has already no entry  followup_sategs table with stage s1
    */
    public function getFollowUpUserDetails($smsStatus, $emailStatus=NULL){
       
        $start = Carbon::now();
        $end = Carbon::now();
        $smsStatusCode = '';
        $malStatusCode = '';
        switch ($smsStatus) {
            case 's1':
                $start = Carbon::now()->subHour(2);
                $end = Carbon::now()->subMinutes(30);
                $smsStatusCode = '168';
                $malStatusCode = '166';
                break;
            case 's2':
                $start = Carbon::now()->subHour(26);
                $end = Carbon::now()->subHour(24);
                $smsStatusCode = '169';
                $malStatusCode = '167';
                break;
            case 's3':
                $start = Carbon::now()->subHour(48);
                $end = Carbon::now()->subHour(46);
                $smsStatusCode = '170';
                break;
            }
       
        //Trigger # 1 User Data
               $s1datas = \Illuminate\Support\Facades\DB::table('users') 
                ->join('user_extra_details','users.id','=','user_extra_details.user_id')        
                ->where('users.created_at', '>=', $start)  
                ->where('users.created_at', '<=', $end)    
                ->where('user_extra_details.complete_status', '=', 0)     
                ->select('users.id')
                ->whereNotExists(function($query)
                {
                $query->select(DB::raw(1))
                ->from('followup_stages')
                ->whereRaw('followup_stages.user_id = users.id')
                ->Where('followup_stages.stage', '=', $smsStatus);
               
                })   
                ->get()->toArray();
                $s1UserData =['data'=>$s1datas,'SMSStatus'=>$smsStatusCode];
                if($s1UserData['data']){           
                    $result =dispatch(new SendSMS($s1UserData));
                }
                if(!empty(emailStatus)){
                    $e1Datas = $this->getFollowUpEmailUserDetails($emailStatus,$start, $end);               
                    $e1UserData =['data'=>$e1Datas,'malStatus'=>$malStatusCode]; 
                    if($e1UserData['data']){
                        $resultEmail = dispatch(new SendEmail($e1UserData));
                    }
                }
                
               
           
        }
    
}
