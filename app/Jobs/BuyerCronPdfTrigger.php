<?php

namespace App\Jobs;

use App\Repositories\Interfaces\PDFGenerationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuyerCronPdfTrigger implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Execute the job
     */
    public function handle(PDFGenerationInterface $PDFGenerationInterface)
    {
        $PDFGenerationInterface->generatePDF($this->user_id);
    }
}
