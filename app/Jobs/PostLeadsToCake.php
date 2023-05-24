<?php
namespace App\Jobs;

use App\Models\BuyerApiResponse;
use App\Models\BuyerApiResponseDetails;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Interfaces\CakeInterface;
use App\Repositories\Interfaces\FollowupSmsEmailEndPointInterface;
class PostLeadsToCake extends Job{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $user_id;
    protected $recordStatus;
    protected $milestone_status;

    public function __construct($user_id,$recordStatus,$milestone_status){
        $this->user_id = $user_id;
        $this->recordStatus = $recordStatus;
        $this->milestone_status = $milestone_status;
    }

    public function handle(CakeInterface $cakeInterface,FollowupSmsEmailEndPointInterface $FollowupSmsEmailEndPointInterface){
      $this->cakeInterface = $cakeInterface;
        $this->FollowupSmsEmailEndPointInterface = $FollowupSmsEmailEndPointInterface;
        $cakePostResult = $this->cakeInterface->cakePost($this->user_id,$buyyerId = NULL);
        if($cakePostResult['result'] == 'Success'){
            $apiResponse = json_encode($cakePostResult);
            $userBankData = $cakePostResult['userBankData'];
            $userBanDataArray = [];
            $resultDetails = $cakePostResult['result_detail'];
            $userSignature = Signature::where('user_id', $this->user_id)->first();
            $userData = User::where('id',$this->user_id)->first();
            foreach ($userBankData as $bankkey => $bankvalue) {
                array_push( $userBanDataArray,$bankvalue->bank_id);
                //$arrSubmitData['lender_' . $i] = $bankvalue->bank_name;
               // $i++;
            }
            $bankIds =  implode(', ', $userBanDataArray);
            $buyerResponseData = BuyerApiResponse::updateOrCreate(
                [
                    'user_id' => $this->user_id,
                ],
               ["lead_id" => $resultDetails['leadid'],
                "result"=>$cakePostResult['result'],
                "api_response"=>$apiResponse,
                "status"=>1,
                "buyer_request_type"=>"CAKE",
                "bank_id"=>$bankIds,
                "signature_id"=>$userSignature->id]
            );
            $buyerResponseDataId = $buyerResponseData->id;
            $buyerResponseData = BuyerApiResponseDetails::updateOrCreate(
                [
                    'buyer_api_response_id' => $buyerResponseDataId,
                ],
                ["lead_value" => $cakePostResult['lead_value'],
                "status"=>1,
                "post_param"=>$cakePostResult['posting_param']]
            );
            $name = $userData->first_name;
            //$smsContent = 'Hello';
            $smsContent = "Dear " . $name. ", thank you for completing the online claim form. We will review your application shortly and come back to you if we require any further details. Please note that we will get in touch with your lender(s) and keep you updated on the progress of your claim(s), but if you want to reach us, please call us on 0207 112 8616 or by email assistance@claimlionlaw.com";
            $emailContent = '<body style="margin: 0px; font-family: sans-serif !important;line-height: 1.5;">
            <header style="background: #ccebfb;padding: 18px 0;border-bottom: 5px solid #004f92;text-align: center;">
               <div class="container">
                  <div class="row">
                     <div class="col-lg-12 text-center">
                        <img src="https://onlineplevincheck.co.uk/assets/CL_PLV1/img/logo.png" alt="CLAIMLION LAW" class="logo" style="text-align: center; height: 50px;border-radius: 4px;padding: 15px 15px;">    
                     </div>
                  </div>
               </div>
            </header>      
            <section class="SECT" style="padding: 30px 0px;margin: 10px;">
               <div class="container-fluid">
                  <div class="row justify-content-center">
                     <div class="col-lg-12 col-md-12 col-sm-12 pad0" id="formpartsec">';
                     $emailContent.= '<p>Dear'." ". $name.', </p>';
                     $emailContent.= '<p>Thank you for providing the relevant details for your potential PPI claims. We will review your application shortly to make sure that all the necessary information has been received. If we require any additional information, we will contact you in due course.</p>
                        <p>If your application is complete, we will submit a Subject Access Request to your lender(s) to obtain the key documentation required for our investigations. Once we have a further update, we will come back to you to inform you about our progress.</p>
                        <p>In the meantime, if you have any queries, please do not hesitate to contact us on 0207 112 8616 or by email on <a href="mailto:assistance@claimlionlaw.com">assistance@claimlionlaw.com</a></p>
                        <p>Many thanks,</p>
                        <p style="margin: 0px;margin-top: 20px;"><strong>Adrian Madar</strong></p>
                        <p style="margin: 0px;"><strong>Claims Manager</strong></p>
                        <p style="margin: 0px;"><strong>ClaimLion Law</strong></p>
                        <p style="margin: 0px;">79 College Road</p>
                        <p style="margin: 0px;">Harrow HA1 1BD</p>                  
                     </div>
                  </div>
               </div>
            </section>
         </body>'; 

         $APP_ENV = env('APP_ENV');
         if (isset($userData->record_status) && $userData->record_status == 'LIVE') {
            if ($APP_ENV == 'live' || $APP_ENV == 'pre') {
               $this->FollowupSmsEmailEndPointInterface->sendStaticEmail($this->user_id,$emailContent);
               $this->FollowupSmsEmailEndPointInterface->sendStaticSMS($this->user_id,$smsContent);
            }
            else{
               $emailContent1 = "Welcome";
               $smsContent1 = "HEllo";
               $this->FollowupSmsEmailEndPointInterface->sendStaticEmail($this->user_id,$emailContent);
               $this->FollowupSmsEmailEndPointInterface->sendStaticSMS($this->user_id,$smsContent1);
            }
         }
      }
        
       



    }
   
}