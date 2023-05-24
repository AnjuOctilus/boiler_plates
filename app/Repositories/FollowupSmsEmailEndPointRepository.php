<?php

namespace App\Repositories;

use App\Repositories\Interfaces\FollowupSmsEmailEndPointInterface;
use App\Repositories\LogRepository;
use DB;
use GuzzleHttp\Client;
use App\Models\User;
use App\Models\FollowupStrategyContent;
use App\Models\FollowupList;
use App\Models\SmsEmailScheduleds;
use App\Models\Followupstages;

class FollowupSmsEmailEndPointRepository implements FollowupSmsEmailEndPointInterface
{
	public function __construct()
	{
		$this->logRepo = new LogRepository();
	}
	/********** Start Followup Send SMS Function **********/
	public function SendSms($arrParamData)
	{
		$userId      	= $arrParamData['user_id'];
		$templateId 	= $arrParamData['template_id'];
		$domain_id  	= $arrParamData['domain_id'];
		$short_url  	= $arrParamData['short_url'];
		$followup_stage = $arrParamData['followup_stage'];
		$user = User::where(['users.id' => $userId])
            ->join('user_banks', 'users.id', '=', 'user_banks.user_id')
            ->join('banks', 'user_banks.bank_id', '=', 'banks.id')
            ->select('users.created_at', 'users.telephone', 'users.token', 'users.first_name', 'banks.bank_name')
            ->first();

        $recipient = trim(preg_replace("/[^0-9]/", "", $user->telephone));
        if(substr($recipient, 0, 1) == 0){
            $trimmed = substr($recipient, 1);
            $recipient = "44" . $trimmed;
        }

        $first_name = $user->first_name;
        $bankName 	= @$user->bank_name;
        $token 		= $user->token;
        $adto_url 	= $short_url."/" . $token . "/".$templateId;
		$appEnv     = env('APP_ENV');

		if ($appEnv == 'live' || $appEnv == 'prod') {
		     $strUrl = 'https://sms.leadfinery.com/api/send';
		} elseif ($appEnv == 'pre') {
		     $strUrl = 'https://pre.sms.leadfinery.com/api/send';
		} else {
		     $strUrl = 'https://dev.sms.leadfinery.com/api/send';
		}

       // $strUrl = env('SMS_SINGLE_ENDPOINT', 'https://dev.sms.leadfinery.com/api/send');
        $dataSMS = FollowupStrategyContent::where(['template_id' => $templateId])->select('content')->first();
        $content = $dataSMS->content;
        //$content = 'Hello,Welcome-'.$templateId;
        if ($content != '') {
            if ($first_name != '') {
                    $content = str_replace('{First Name}', $first_name, $content);
            } else {
                    $content = $content;
            }
            if ($adto_url != '') {
                $content = str_replace('{adtourl}', $adto_url, $content);
            } else {
                    $content = $content;
            }
            if ($bankName != '') {
                    $content = str_replace('{bank}', $bankName, $content);
            } else {
                    $content = $content;
            }
        }
        $header = array(
		    'Authorization' => 'Bearer ' . env('ADTOPIA_TOKEN'),
		);
		$arrData = array(
		    "ProjectCode" => env('ADTOPIA_UPID'),
		    "Environment" => strtoupper($appEnv),
		    "SmsDetails" => array(
		        "To" => $recipient,
		        "Message" => $content,
		    ),
		);
		$apiRequestParams = [
            'headers' => $header,
            'json' => $arrData,
        ];
        $insertArr = [
        	'domain_id' => $domain_id,
        	'template_id' => $templateId,
        	'stage' => $followup_stage,
        ];
        $send_sms = $this->SendSmsToRecipient($strUrl,$apiRequestParams,$insertArr,$userId);
	}
	/********** End Followup Send SMS Function **********/
  	public function SendSmsToRecipient($strUrl,$apiRequestParams,$insertArr,$userId){
  		$client = new Client();
          $templateId = isset($insertArr['template_id'])?$insertArr['template_id']:'';
        try {
            $strResult = 'Success';
            $response = $client->request('POST', $strUrl, $apiRequestParams);
            $strMessage = $response->getBody()->getContents();
            $status = substr($strMessage, 0, 3);
            if ($status == 'ERR') {
                $strResult = 'Failed';
            }
        } catch (\Exception $e) {
            $strMessage = $e->getMessage();
            $strResult = 'Failed';
        }
        $data_Arr['status'] 		= $strResult;
        $data_Arr['response'] 		= $strMessage;
        $data_Arr['domain_id']		= $insertArr['domain_id'];
        $data_Arr['user_id'] 		= $userId;
        $data_Arr['atp_url_id'] 	= $insertArr['template_id'];
        $followupStage['user_id']   = $userId;
        $followupStage['stage']     = $insertArr['stage'];
        $followupStage['created_at']     = date('Y-m-d H:i:s');
        $followupStage['updated_at']     = date('Y-m-d H:i:s');
        $data_Arr['type'] = 'SMS';
        if ($strResult == 'Success') {
            $objMessage = json_decode($strMessage);
        }
        $insrt_id = SmsEmailScheduleds::insertGetId($data_Arr);
        if($strResult == 'Success'){
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n strMessage : " . $strMessage . " \n --Template Id -" . $templateId ." \n --User data_Arr -" . json_encode($data_Arr) . "------\n";
            $logWrite = $this->logRepo->writeLog('-user_followup_send_sms', $strFileContent);
        }
        else{
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n strMessage : " . $strMessage ." \n --Template Id -" . $templateId . " \n --User data_Arr -" . json_encode($data_Arr) . "------\n";
            $logWrite = $this->logRepo->writeLog('-user_followup_send_sms', $strFileContent);
        }
        Followupstages::insertGetId($followupStage);
    }
	/********** Start Followup Send Email Function **********/
  	public function SendEmail($arrParamData){
  		$user_id        = $arrParamData['user_id'];
		$template_id    = $arrParamData['template_id'];
		$followup_stage = @$arrParamData['followup_stage'];
		$domain_id  	= $arrParamData['domain_id'];
		$short_url  	= $arrParamData['short_url'];
  		$user = User::where(['users.id' => $user_id])
            ->join('user_banks','users.id','=','user_banks.user_id')
            ->join('banks','user_banks.bank_id','=','banks.id')
            ->select('users.created_at','users.telephone','users.token','users.first_name','users.email','banks.bank_name')
            ->first();
                    
  		if ($user) {
            $strategy_type = '';
            $type = 'email';
            if($type == 'email') {
            	$followupList = new  FollowupList();
                $followupList->user_id = $user_id;
                $followupList->lead_date = date($user['created_at']);
                $followupList->type      = $type;
                $followupList->token     = $user['token'];
                $followupList->save();
             	$token      = $user->token;
            	$first_name = $user->first_name;
             	$bankName   = $user->bank_name;
             	$to_email   = $user->email;
         		$recipient 	= $user->telephone;
             	$email_url 	= $short_url."/" . $token . "/".$template_id;
           		$appEnv     = env('APP_ENV');
           		//$appEnv = 'DEV';

				if ($appEnv == 'live' || $appEnv == 'prod') {
				     $strUrl = 'https://email.leadfinery.com/api/send';
				} elseif ($appEnv == 'pre') {
				     $strUrl = 'https://pre.leadfinery.com/api/send';
				} else {
				     $strUrl = 'https://dev.email.leadfinery.com/api/send';
				}

                //$strUrl = env('EMAIL_SINGLE_ENDPOINT', 'https://dev.email.leadfinery.com/api/send');
				$header = array(
				    'Authorization' => 'Bearer ' . env('ADTOPIA_TOKEN'),
				);

				$dataEmail =  FollowupStrategyContent::where(['template_id' => $template_id])->select('content','subject')->first();
				$subject = str_replace('{Bank}', $bankName, $dataEmail->subject);
				$content = $dataEmail->content;
               // $content = "Hello, Welcome!";
				if ($content != '') {
	               if(!empty($first_name)){
	                   $content = str_replace('{First Name}', $first_name, $content);
	               }
	                if ($email_url) {
                        
	                    $content = str_replace('{adtourl}', $email_url, $content);
	                }

	                if ($bankName) {
	                    $content = str_replace('{Bank}', $bankName, $content);
	                }
                    
	            }
               
				$attachment = '';
				$arrData = array(
				    "ProjectCode" => env('ADTOPIA_UPID'),
				    "Environment" => strtoupper($appEnv),
				    "EmailDetails" => array(
				    	"From" =>  array(
					    	"Name" => 'ClaimLion Law',
					        "Email" => 'yourclaim@claimlionlaw.com'
					    ),
				        "To" => array(
				        	[
				        	"Name" => $first_name,
					        "Email" => $to_email
                            ]
				        ),
				        "Subject" => $subject,
				        "Body" => $content,
				        "Attachments" => array(
				        	$attachment
				        )
				    ),

				);
               
		$apiRequestParams = [
            'headers' => $header,
            'json'    => $arrData,
        ];

         $insertArr = [
        	'domain_id'   => $domain_id,
        	'template_id' => $template_id,
        	'stage'       => $followup_stage,
        ];

        // Do request call
        $send_sms = $this->send_email_strategy($strUrl,$apiRequestParams,$insertArr,$user_id);

            }
        }
  	}
	/********** End Followup Send Email Function **********/
  	public function send_email_strategy($strUrl,$apiRequestParams,$insertArr,$user_id){
        $logRepository = new LogRepository;
        $client = new Client();
        $templateId = isset($insertArr['template_id'])?$insertArr['template_id']:'';
        try {
            $strResult = 'Success';
            $response = $client->request('POST', $strUrl, $apiRequestParams);
            $strMessage = $response->getBody()->getContents();
            $status = substr($strMessage, 0, 3);
            

            if ($status == 'ERR') {
                echo "Try error";
                $strResult = 'Failed';
            }
            
        } catch (\Exception $e) {
            $strMessage = $e->getMessage();
            //$logWrite      = $logRepository->writeLog('Error',$strMessage);
            $logWrite      = $logRepository->writeLog('success',$templateId);
            $strResult = 'Failed';
        }
		$data_Arr['status'] 		= $strResult;
        $data_Arr['response'] 		= $strMessage;
        $data_Arr['domain_id']		= $insertArr['domain_id'];
        $data_Arr['user_id'] 		= $user_id;
        $data_Arr['atp_url_id'] 	= $insertArr['template_id'];
        $followupStage['user_id']   = $user_id;
        $followupStage['stage']     = $insertArr['stage'];
        $followupStage['created_at']     = date('Y-m-d H:i:s');
        $followupStage['updated_at']     = date('Y-m-d H:i:s');
        $data_Arr['type'] = 'Email';
        $insrt_id = SmsEmailScheduleds::insertGetId($data_Arr);

        
        if($strResult == 'Success'){
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n strMessage : " . $strMessage . " \n --Template Id -" . $templateId ." \n --User data_Arr -" . json_encode($data_Arr) . "------\n";
            $logWrite = $this->logRepo->writeLog('-user_followup_send_sms', $strFileContent);
        }
        else{
            $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s') . "\n strMessage : " . $strMessage ." \n --Template Id -" . $templateId . " \n --User data_Arr -" . json_encode($data_Arr) . "------\n";
            $logWrite = $this->logRepo->writeLog('-user_followup_send_sms', $strFileContent);
        }
        
        Followupstages::insertGetId($followupStage);
      
        //return $data_Arr;
    }


    public function sendStaticSMS($userId, $content){
        $userdata = User::where('id',$userId)->first();

        $recipient = trim(preg_replace("/[^0-9]/", "", $userdata->telephone));
        if(substr($recipient, 0, 1) == 0){
            $trimmed = substr($recipient, 1);
            $recipient = "44" . $trimmed;
        }

        $appEnv         = env('APP_ENV');
        //$strUrl = env('SMS_SINGLE_ENDPOINT', 'https://dev.sms.leadfinery.com/api/send');

        if ($appEnv == 'live' || $appEnv == 'prod') {
            $strUrl = 'https://sms.leadfinery.com/api/send';
        } elseif ($appEnv == 'pre') {
            $strUrl = 'https://pre.sms.leadfinery.com/api/send';
        } else {
            $strUrl = 'https://dev.sms.leadfinery.com/api/send';
        }

        //$content = 
        $header = array(
                    'Authorization' => 'Bearer ' . env('ADTOPIA_TOKEN'),
                );
                $arrData = array(
                    "ProjectCode" => env('ADTOPIA_UPID'),
                    "Environment" => strtoupper($appEnv),
                    "SmsDetails" => array(
                        "To" => $recipient,
                        "Message" => $content,
                    ),
                );

        $apiRequestParams = [
                    'headers' => $header,
                    'json' => $arrData,
                ];

        $client = new Client();
        try {
            $strResult = 'Success';
            $response = $client->request('POST', $strUrl, $apiRequestParams);
            $strMessage = $response->getBody()->getContents();
            $status = substr($strMessage, 0, 3);
            if ($status == 'ERR') {
                $strResult = 'Failed';
            }
        } catch (\Exception $e) {
            $strMessage = $e->getMessage();
            $strResult = 'Failed';
            $logWrite = $this->logRepo->writeLog('Realtime SMS Error', $strMessage);
        }
    }


    public function sendStaticEmail($userId, $content){
        $userdata = User::where('id',$userId)->first();
        $appEnv = env('APP_ENV');

        if ($appEnv == 'live' || $appEnv == 'prod') {
            $strUrl = 'https://email.leadfinery.com/api/send';
        } elseif ($appEnv == 'pre') {
            $strUrl = 'https://pre.leadfinery.com/api/send';
        } else {
            $strUrl = 'https://dev.email.leadfinery.com/api/send';
        }

        //$strUrl = env('EMAIL_SINGLE_ENDPOINT', 'https://dev.email.leadfinery.com/api/send');

        $header = array(
        'Authorization' => 'Bearer ' . env('ADTOPIA_TOKEN'),
        );

        $attachment = '';
        $subject = 'CLAIMLION LAW: Application Recieved';
        $arrData = array(
        "ProjectCode" => env('ADTOPIA_UPID'),
        "Environment" => strtoupper($appEnv),
        "EmailDetails" => array(
            "From" =>  array(
                "Name" => 'ClaimLion Law',
                "Email" => 'yourclaim@claimlionlaw.com'
            ),
            "To" => array(
                [
                "Name" => $userdata->first_name,
                "Email" => $userdata->email
                ]
            ),
            "Subject" => $subject,
            "Body" => $content,
            "Attachments" => array(
                $attachment
            )
        ),

     );
                    
        $apiRequestParams = [
            'headers' => $header,
            'json'    => $arrData,
        ];

        $client = new Client();
            try {
                $strResult = 'Success';
                $response = $client->request('POST', $strUrl, $apiRequestParams);
                $strMessage = $response->getBody()->getContents();
                $status = substr($strMessage, 0, 3);
                if ($status == 'ERR') {
                    $strResult = 'Failed';
                }
            } catch (\Exception $e) {
                $strMessage = $e->getMessage();
                $strResult = 'Failed';
                $logWrite = $this->logRepo->writeLog('Realtime Email Error', $strMessage);
            }

    }
}
