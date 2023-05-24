<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Repositories\Interfaces\PDFGenerationInterface;

class GenerateLOAPDF extends Job{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $customerId;
    protected $bankData;
    protected $userBank;
    protected $key;
    protected $userBankName;
    protected $userBankId;
    protected $userBankDetails;
    protected  $count;
    public function __construct($userId,$key,$userBankName,$userBankId,$count)
    {
        $this->userId = $userId;
       // $this->userBank = $userBank;
        $this->key = $key;
        $this->userBankName = $userBankName;
        $this->userBankId = $userBankId;
        $this->count = $count;
        
    }

    public function handle(PDFGenerationInterface $PDFGenerationInterface){
        echo "===========Inside handle==========";echo "\n";
        $this->userBankDetails = [];
        $this->userBankDetails['bank_id'] = $this->userBankId;
        $this->userBankDetails['bank_name'] = $this->userBankName;
        $this->userBankDetails['count'] = $this->count;
        echo "================bankName=========".$this->userBankDetails['bank_id'];echo "\n";
        $this->pdfGenerationInterface = $PDFGenerationInterface;
        $engagementPdf = $this->pdfGenerationInterface->generateEngagementPDF($this->userId,null,null,null,$this->userBankDetails,$this->key);
      
    }
}