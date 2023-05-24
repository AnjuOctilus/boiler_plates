<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\BuyerLOAPdfTrigger;
use App\Console\Commands\FollowUpEmailSMSStrategy;
use App\Console\Commands\FollowUpEmailSMSStrategyFourtySix;
use App\Console\Commands\FollowUpEmailSMSStrategyPending;
use App\Console\Commands\FollowUpEmailSMSStrategyTwentyFour;
use App\Console\Commands\RecreateCOAFile;
use App\Console\Commands\AddressDetails;
use App\Console\Commands\SMSEmail;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class,
        BuyerLOAPdfTrigger::class,
        FollowUpEmailSMSStrategy::class,
        FollowUpEmailSMSStrategyFourtySix::class,
        FollowUpEmailSMSStrategyTwentyFour::class,
        FollowUpEmailSMSStrategyPending::class,
        RecreateCOAFile::class,
        AddressDetails::class,
        SMSEmail::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        
        $schedule->command('Api:FollowUpEmailSMSStrategy')->cron('*/30 * * * *');//Every half an hour
        $schedule->command('Api:FollowUpEmailSMSStrategyTwentyFour')->cron('*/30 * * * *');//every 24 hours
        $schedule->command('Api:FollowUpEmailSMSStrategyFourtySix')->cron('*/30 * * * *');//every 46 hours
        //$schedule->command('Api:RecreateCOAFile')->cron('*/10 * * * *');//Every 10 Minutes
        $schedule->command('Api:AddressDetails')->cron('* 30 * * * *');
        $schedule->command('Api:SMSEmail')->cron('* 30 * * * *');
    }
}
