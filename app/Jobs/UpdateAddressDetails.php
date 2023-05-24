<?php

namespace App\Jobs;

use App\Repositories\Interfaces\UpdateAddressInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAddressDetails implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $user_id;
    protected $start;
    protected $end;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$start, $end)
    {
        $this->user_id = $user_id;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Execute the job
     */
    public function handle(UpdateAddressInterface $updateAddressInterface)
    {
        $updateAddressInterface->updateDetails($this->user_id, $this->start,  $this->end);
    }
}
