<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Repositories\LogRepository;
use App\Models\SiteConfig;



// use App\Repositories\CommonFunctionsRepository;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Api:TestCron';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Test Cron';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    public function __construct()
    {
        parent::__construct();
    }

    /** 
     * Execute the console command.
     *
     *@return mixed
     */

    public function handle()
    {
        $logRepo    = new LogRepository;
        $strFileContent = "\n----------\n Date: " . date('Y-m-d H:i:s');
        $logWrite   = $logRepo->writeLogIntoS3('-TestCron-log', $strFileContent);

        $arrData = array("config_title" =>  "CRON", "config_value" => "SUCCESS", "config_info" => date('Y-m-d H:i:s'));
        SiteConfig::updateOrCreate(
            [    
                'config_title' => "CRON"
            ],
            $arrData
        );
    }
}
