<?php

namespace App\Jobs;

use App\Models\BuyerApiResponse;
use App\Models\BuyerApiResponseDetails;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Interfaces\PDFGenerationInterface;

class RegeneratePdf extends Job{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $customerId;
    protected $bankData;

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
       //$engagementPdf = $this->pdfGenerationInterface->generateEngagementPDF($this->userId,$this->customerId,$this->bankData,true);
       //$authenticityPdf = $this->pdfGenerationInterface->generateAuthenticityPDF($this->userId,$this->customerId,$this->bankData,true);
       $questionnairePdf = $this->pdfGenerationInterface->generateQuestionnairePDF($this->userId,$this->customerId,$this->bankData,true);
       $previewPdf =  $this->pdfGenerationInterface->generatePreviewPDF($this->userId,$this->customerId,$this->bankData,true);
       $engagementPdf = $this->pdfGenerationInterface->generateStatementPDF($this->userId,$this->customerId,$this->bankData,true);


    }

}