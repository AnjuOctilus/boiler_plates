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

class GeneratePdfApi extends Job{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;

    public function __construct($data){
        $this->userId = $data["user_id"];
         
    }

    public function handle(PDFGenerationInterface $PDFGenerationInterface){
        $this->pdfGenerationInterface = $PDFGenerationInterface;
        //$engagementPdf = $this->pdfGenerationInterface->generateEngagementPDF($this->userId);
       $authenticityPdf = $this->pdfGenerationInterface->generateAuthenticityPDF($this->userId);
       $questionnairePdf = $this->pdfGenerationInterface->generateQuestionnairePDF($this->userId);
       $previewPdf =  $this->pdfGenerationInterface->generatePreviewPDF($this->userId);
       $statementPdf = $this->pdfGenerationInterface->generateStatementPDF($this->userId);
    }

}