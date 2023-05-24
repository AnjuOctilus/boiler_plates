<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Interfaces\PDFGenerationInterface;
use App\Repositories\UserRepository;

class RegenerateLoaPDFApi extends Job{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $customerId;
    protected $bankData;
    protected $userBankDetails;

    public function __construct($request){
       $this->userId = $request->user_id;
       $this->customerId = $request->customer_id;

       $bankData = $request->bank_data;
       
       if (is_iterable($bankData)) {
            foreach($bankData as $index => $value) {
                $bankId = $value['bank_id'] ?? '';
                $claimId = $value['claim_id'] ?? '';

                $this->bankData[$bankId] = $claimId;
            }
       }
       
        //$this->userId = 156;
        //$this->customerId = 7889;
        //$this->bankData = [1 => 'sdfdfds', 24 => 'sdfszgdf'];
        
    }
    public function handle(PDFGenerationInterface $PDFGenerationInterface)
    
    {
        $this->pdfGenerationInterface = $PDFGenerationInterface;
        $userRepository = new UserRepository();
        $userBanks = $userRepository->getUserDetailsFromUserId($this->userId);
                $i=0;
                foreach($userBanks as $key=>$userBank){
                    $i++;
                    $this->userBankDetails = [];
                    $this->userBankDetails['bank_id'] = $userBank['bank_id'];
                    $this->userBankDetails['bank_name'] = $userBank['bank_name'];
                    $this->userBankDetails['count'] = $i;
                    $engagementPdf = $this->pdfGenerationInterface->generateEngagementPDF($this->userId,$this->customerId,$this->bankData,true,$this->userBankDetails,$key);
                }
       
       

    }

}
