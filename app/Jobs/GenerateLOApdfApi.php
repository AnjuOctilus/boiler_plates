<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Interfaces\PDFGenerationInterface;
use App\Repositories\UserRepository;

class GenerateLOApdfApi extends Job{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $userBankDetails;

    public function __construct($request){
       $this->userId = $request["user_id"];
     
       
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
                    $engagementPdf = $this->pdfGenerationInterface->generateEngagementPDF($this->userId,null,null,null,$this->userBankDetails,$key);
                }
       
       

    }

}
