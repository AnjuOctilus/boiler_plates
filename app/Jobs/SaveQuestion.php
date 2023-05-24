<?php

namespace App\Jobs;

use App\Repositories\Interfaces\LeadSubmissionApiInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SaveQuestion implements ShouldQueue
{
    use  InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    protected $uuid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$uuid)
    {
        $this->data = $data;
        $this->uuid = $uuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle( LeadSubmissionApiInterface $LeadSubmissionApiInterface) {
        $this->LeadSubmissionApiInterface = $LeadSubmissionApiInterface;
        $this->LeadSubmissionApiInterface->storeQuestion($this->data,$this->uuid);
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
