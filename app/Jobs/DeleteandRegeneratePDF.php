<?php
namespace App\Jobs;

use App\Models\LeadDoc;
use App\Models\ProcessedCOAPdf;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Interfaces\PDFGenerationInterface;
use DB;
use App\Repositories\UserRepository;
class DeleteandRegeneratePDF extends Job{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $leadDocsId;
    protected $userId;
   

    public function __construct($leadDocsId,$userId)
    {
      $this->leadDocsId = $leadDocsId; 
      $this->userId = $userId;
        
    }

    public function handle(PDFGenerationInterface $PDFGenerationInterface){
        $this->pdfGenerationInterface = $PDFGenerationInterface;
        if(isset($this->leadDocsId) && !empty($this->leadDocsId)){
            echo "===============LeadDocsId=================".$this->leadDocsId;echo "\n";
            $leadDocsPdfFiles = LeadDoc::updateOrCreate(
                [   'id' => $this->leadDocsId],
                [   "pdf_file" => null,
                ]
            );
            $leadDocsPdfFiles = ProcessedCOAPDF::updateOrCreate(
                [   'lead_docs_id' => $this->leadDocsId],
                [   "user_id" => $this->userId,
                    "created_at"=>new \DateTime(),
                    "updated_at"=>new \DateTime()
                ]
            );
            if(isset($this->userId)){
                $authenticityPdf = $this->pdfGenerationInterface->generateAuthenticityPDF($this->userId);
            }
           

        

    }

    }

}