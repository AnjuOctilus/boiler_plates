<?php

namespace App\Console\Commands;

use App\Models\CronMapping;
use App\Models\PdfTrigger;
use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
class FollowUpEmailSMSStrategyFourtySix extends  \Illuminate\Console\Command{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Api:FollowUpEmailSMSStrategyFourtySix';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Followup Email-SMS strategy for ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $getUserRepository = new UserRepository;
        $getUserRepository->getFollowUpUserDetailsS3();
        

    }
}

