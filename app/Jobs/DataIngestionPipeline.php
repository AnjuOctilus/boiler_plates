<?php

namespace App\Jobs;

use App\Repositories\Interfaces\AdvDataIngestionInterface;
use App\Repositories\Interfaces\QuestionnairesInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\LPDataIngestionInterface;
use App\Repositories\Interfaces\SignatureDataIngestionInterface;
use App\Repositories\Interfaces\FollowupDataIngestionInterface;
use App\Repositories\Interfaces\UserDocumentsDataIngestionInterface;
use App\Repositories\Interfaces\PDFGenerationInterface;
use Carbon\Carbon;

class DataIngestionPipeline implements ShouldQueue
{
    use  InteractsWithQueue, Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        LPDataIngestionInterface $lPDataIngestionInterface,
        AdvDataIngestionInterface $advDataIngestionInterface,
        QuestionnairesInterface $questionnairesInterface,
        FollowupDataIngestionInterface $followupDataIngestionInterface,
        SignatureDataIngestionInterface $signatureDataIngestionInterface,
        UserDocumentsDataIngestionInterface $userDocumentsDataIngestionInterface,
        PDFGenerationInterface $PDFGenerationInterface
    ) {
        $this->lPDataIngestionInterface = $lPDataIngestionInterface;
        $this->advDataIngestionInterface = $advDataIngestionInterface;
        $this->questionnairesInterface = $questionnairesInterface;
        $this->followupDataIngestionInterface = $followupDataIngestionInterface;
        $this->signatureDataIngestionInterface = $signatureDataIngestionInterface;
        $this->userDocumentsDataIngestionInterface = $userDocumentsDataIngestionInterface;
        $this->PDFGenerationInterface = $PDFGenerationInterface;
        Log::info('Inside Queue log dev ');
        $message_type = $this->data['message_type'];
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        switch ($message_type) {
            case 'split_page_load':
                $queryString = isset($this->data['query_string']) ? $this->data['query_string'] : '';
                $this->lPDataIngestionInterface->commonSplits($this->data['data'], $this->data['visitor_parameters'], $this->data['currentTime'], $this->data['page_name'], $queryString);
                break;
            case 'split_form_submit':
                $queryString = isset($this->data['query_string']) ? $this->data['query_string'] : '';
                $this->lPDataIngestionInterface->store($this->data['data'], $queryString, $this->data['visitor_parameters'], $this->data['currentTime'], $this->data['page_name'], $this->data['visitor_data']);
                break;
             case 'signature_store' :
                $queryString = isset($this->data['query_string'])? $this->data['query_string'] : '';
                $this->signatureDataIngestionInterface->store($this->data['s3_signatureData'],$this->data['visitor_parameters'],@$this->data['previous_data'],$this->data['form_data'],$this->data['data'],$queryString);
                //$this->FollowupDataIngestionRepository->signatureStore($this->data['s3_signatureData'], $this->data['visitor_parameters'],null,$currentTime);
                break;
            case 'followup_user_signature' :
                $this->followupDataIngestionInterface->signatureStore($this->data['s3_signatureData'],$currentTime,$this->data['followup_data']);
                break;
            case 'adv_page_load':
                $this->advDataIngestionInterface->saveADVVisitorData($this->data['data'], $this->data['visitor_parameters'], $this->data['page_name']);
                break;
            case 'adv_click':
                $this->advDataIngestionInterface->saveAdvClicks($this->data['data'], $this->data['visitor_parameters'], $this->data['page_name']);
                break;
          case 'followup_load':

                $queryString = isset($this->data['query_string'])? $this->data['query_string'] : '';
                $this->followupDataIngestionInterface->saveFollowUpData($this->data,$queryString,$this->data['currentTime']);
                break;
            /*case 'followup_user_signature':

                $this->followupDataIngestionInterface->signatureStore($this->data['signature_data'],$this->data['followup_data'],@$this->data['previous_data'],$this->data['currentTime']);
                break;*/

            case 'followup_question_store':        
                $this->followupDataIngestionInterface->questionStore($this->data['question_data'],$this->data['followup_data']);
                break;
                //die;*/
            case 'question_store':
                $queryString = isset($this->data['query_string'])? $this->data['query_string'] : '';
                $this->questionnairesInterface->saveQuestionaires($this->data['visitor_parameters'],$this->data['question_data'],$this->data['form_data'],$this->data['data'],$queryString);
                break;
            case 'user_docs_store':
                $queryString = isset($this->data['query_string'])? $this->data['query_string'] : '';
                $this->userDocumentsDataIngestionInterface->store($this->data['user_document_data'],$this->data['visitor_parameters'],$this->data['form_data'],$this->data['data'],$queryString);
                break;  
        }
    }

    /**
     * Tags
     *
     * @return array
     */
    public function tags()
    {
        return [$this->data];
    }
}
