<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\SmsEmailScheduleds;
use App\Repositories\Interfaces\CommonFunctionsInterface;
use App\Repositories\Interfaces\UserInterface;
use Config\constant;
use App\Repositories\Interfaces\FollowupSmsEmailEndPointInterface;
class SendSMSJob extends Job
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    /**     
     * Create a new job instance. 
     *
     * @return void
     */
    public function __construct($user)
    {        
        
        $this->user = $user;

    
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CommonFunctionsInterface $commonfunctionsinterface,UserInterface $userinterface, 
    FollowupSmsEmailEndPointInterface $smsEmailSingleRepo)
    {    
        echo "=============Handle==========================";echo "\n";
        $smsUrl = config('constants.SMS_EMAIL_SHORT_URL');
       $users = $this->user['data'];
       
       //dd($this->user);
       $SMSStages =  isset( $this->user['SMSStatus'])? $this->user['SMSStatus']:'';
       $MailStages = isset( $this->user['malStatus'])? $this->user['malStatus']:'';
       $this->commonfunctionsinterface = $commonfunctionsinterface;   
       $this->UserInterface = $userinterface; 
       $this->smsEmailFollowup   = $smsEmailSingleRepo;
       echo "=============Before conditions==========================";echo "<br/>";
    //Get SMS template Details
    if($SMSStages){
       $SMSTemplate = \Illuminate\Support\Facades\DB::table('followup_strategy_content as template')->where('template.template_id',$SMSStages)->first();
    }else{
        $SMSTemplate =""; 
    }      
    //Get Mail template Details
      if($MailStages){
       $MailTemplate = \Illuminate\Support\Facades\DB::table('followup_strategy_content as template')->where('template.template_id',$MailStages)->first();      
    }else{
        $MailTemplate =''; 
    }   
   
        $result = [];
        $userDatas = [];           
        //User URL Checking
        if ($users) {        
            foreach ($users as $user){  
                $signaturData = \Illuminate\Support\Facades\DB::table('signatures')
                                 ->where('user_id', $user->id)->get()->count();
                $QustionData = \Illuminate\Support\Facades\DB::table('user_questionnaire_answers as uqa')                                                
                                ->where('user_id', $user->id)->get()->count(); 
                   if(  $signaturData == 0)  {
                        $url = $smsUrl.'/';               
                        $userDatas[$user->id] = $url;
                    }else if($QustionData <= '9') {
                        $url = $smsUrl.'/';                                                    
                        $userDatas[$user->id] = $url; 
                    }                 
            }                
        }   
    //Mail And SMS 
        if($userDatas){
           $this->sendMailandSMS($userDatas,$MailTemplate,$SMSTemplate,$SMSStages,$MailStages);         
        }
       
    }
    /**
     * Prepare content for SMS and EMail
     * @param $userDatas Array
     * @param $MailTemplate string
     * @param $SMSTemplate string
     * @param $SMSStages string - template id
     * @param $MailStages string - template id
     */
    public function sendMailandSMS($userDatas,$MailTemplate,$SMSTemplate,$SMSStages,$MailStages){
        $mailStage =[];
        $mailResp =[];
        $resultError = [];
        $resultSuccess = [];
        $APP_ENV = env('APP_ENV');
        if ($APP_ENV == 'live' || $APP_ENV == 'prod') {
            $shortUrl = "https://adto.uk";
        } elseif($APP_ENV == 'pre'){
            $shortUrl = "https://pre.adto.uk";
        }
        else {
            $shortUrl = "https://dev.adto.uk";
        }

        foreach ($userDatas as $key => $value){
            $data = \Illuminate\Support\Facades\DB::table('users as user')          
                ->where('user.id', $key)                
                ->select('user.*')
                ->first();    
                
            //recipient
                $SMSRecipient = $data->telephone;                
                $mailRecipient =$data->email;
            //Subject
                if($MailTemplate){
                    $mailSubject=$MailTemplate->subject;
            //Content
                    $userBank = $this->UserInterface->getUserBankDetails($data->id);
                    $bank = $userBank[0]->banks;                       
                    $url =$value.$data->token.'/'.$MailStages;  
                    $inpContent  = ["{First Name}", "{adtourl}", "{Bank}" ,"[ Click Here to Read ]"];
                    $outContent  = [$data->first_name, $url, $bank,$url];
                    $contentRPLS = str_replace($inpContent , $outContent, $MailTemplate->content);
                    // $mailContent = $content1.$content2.$content3.$content4;   
                }
                if($SMSTemplate){
                    $SMSContent = $data->first_name.','.$SMSTemplate->content.' '.$value.$data->token.'/'.$SMSStages; 
                }

                if($SMSStages == 168){  
                    $SMSStage = 'S1';
                }
                else if($SMSStages == 169){
                    $SMSStage = 'S2';                
                }
                else if($SMSStages == 170){
                    $SMSStage = 'S3';                                      
                }
                if(isset($SMSStage)){
                    $smsCronStatus  = $this->getCronJobStatus($SMSStage);
                }
               
           //Send SMS
           if($SMSTemplate){
                if(isset($smsCronStatus->status) && $smsCronStatus->status == 1){
                    $SMSResp  = $this->smsEmailFollowup->SendSms(array('user_id' =>$data->id,'template_id' =>$SMSStages,'domain_id'=>1,'short_url'=>$shortUrl,'followup_stage'=>$SMSStage));
                }
                else{
                    echo "Email CronJob is Disabled";
                    return null;
                }
            }
        }  
        
    }
    /**
     * Update SMS and Email data on sms_schedule table and followup_stages on return success
     * $SMSResp - SMS send function response
     * mailResp - Email Send function response
     * $data - userdata array
     * $SMSStage - SMS stage
     * $mailStage -  Email Stage
     */
    public function updateDataByResponse($SMSResp,$mailResp,$data,$SMSStage,$mailStage,$MailTemplate){
        $resultError = [];
        $resultSuccess = [];        
        //Keep SMS Response
        if($SMSResp['response'] == true){
            $followupData=\Illuminate\Support\Facades\DB::table('followup_stages')->insert([
                'user_id' => $data->id,
                'stage' => $SMSStage,
                'created_at' =>new \DateTime(),
                'updated_at' =>new \DateTime(),                     
            ]);
            $newUser =SmsEmailScheduleds::updateOrCreate([                        
                'user_id'   => $data->id,
                'sms_batch_id'=>$SMSStage,
            ],[
                'user_id' => $data->id,                        
                'status'  => 1,
                'response'  => $SMSResp['response'], 
                'scheduled_date'=>new \DateTime(),
                'sms_batch_id'=>$SMSStage,
                'created_at' =>new \DateTime(),
                'updated_at' =>new \DateTime(),
            ]);
            array_push($resultSuccess,$data->id);
          }else{
            array_push($resultError,$data->id);
          }
        //Keep Mail Response
        if($MailTemplate){ 
          if($mailResp['response'] == true){
            $followupData=\Illuminate\Support\Facades\DB::table('followup_stages')->insert([
                'user_id' => $data->id,
                'stage' => $mailStage,
                'created_at' =>new \DateTime(),
                'updated_at' =>new \DateTime(),                     
            ]);    
            SmsEmailScheduleds::where('user_id',$data->id)
                ->where('sms_batch_id', $SMSStage)
                ->update(['email_batch_id'=>$mailStage]);
          
            array_push($resultSuccess,$data->id);
          }else{
            array_push($resultError,$data->id);

          }
        }

    }
    /**
     * Return CronJob Status
     */
    public function getCronJobStatus($stage){
        if($stage == 'S1'){
            $name = 'FollowUpSmsStrategyCronNew';
        }
        elseif($stage = 'E1'){
            $name = 'FollowUpEmailStrategyCronNew';
        }
        elseif($stage == 'S2'){
            $name = 'FollowUpSmsStrategy24HrsCronNew';

        }
        elseif($stage = 'E2'){
            $name = 'FollowUpEmailStrategy24HrsCronNew';
        }
        elseif($stage == 'S3'){
            $name = 'FollowUpSmsStrategy48HrsCronNew';

        }
        else{
            $name = '';  
        }
        if(isset($name) && !empty($name)){
            $data = \Illuminate\Support\Facades\DB::table('cron_mappings as cm')
                    //->leftJoin('user_questionnaire_answers as uqa', 'que.id', '=', 'uqa.questionnaire_id')
                    ->where('cm.name', $name)
                    ->select('cm.status')
                    ->get()->first();
                    echo '<pre>';
                    print_r($data);
                    echo '</pre>';
                return $data;
        }
    }
}

