<?php

namespace App\Jobs;

use App\Repositories\Interfaces\SignatureRestoreInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RestoreSignature implements ShouldQueue
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
    public function handle( SignatureRestoreInterface $SignatureRestoreInterface) {
        $this->SignatureRestoreInterface = $SignatureRestoreInterface;
        $this->SignatureRestoreInterface->signatureStore($this->data);
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
