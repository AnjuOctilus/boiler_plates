<?php

namespace App\Console\Commands;

use App\Models\CronMapping;
use App\Models\PdfTrigger;
use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Repositories\EmailSMSInterface;
class SMSEmail extends  \Illuminate\Console\Command{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Api:SMSEmail';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Followup Email-SMS strategy for all type';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $crons = CronMapping::where('name', '=', 'SMSEmail')
        ->where('status', '=', 1)
        ->first();
        if (!empty($crons)) {
            $getUserRepository = new EmailSMSInterface;
            $getUserRepository->getFollowUpUserDetails('s1','e1');
            $getUserRepository->getFollowUpUserDetails('s2','e2');
            $getUserRepository->getFollowUpUserDetails('s3');
        }
        

    }
}

