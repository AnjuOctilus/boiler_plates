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
    protected $doc_type;
    protected $leadDocsId;

    public function __construct($request){
       $this->userId = $request->user_id;
       $this->customerId = $request->customer_id;
       $this->doc_type = $request->doc_type;
       $this->bankData = $request->bank_data;
       $this->leadDocsId = $leadDocsId;
       
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
        switch ($this->doc_type) {
            case 'loa':
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
                break;
            case 'questionnaire':
                $questionnairePdf = $this->pdfGenerationInterface->generateQuestionnairePDF($this->userId,$this->customerId,$this->bankData,true);
                break;
             case 'preview' :
                $previewPdf =  $this->pdfGenerationInterface->generatePreviewPDF($this->userId,$this->customerId,$this->bankData,true);      
                break;
            case 'statement' :
                $this->pdfGenerationInterface->generateStatementPDF($this->userId,$this->customerId,$this->bankData,true);
                break;
          
        }
       
       
    }
       

    }

}

