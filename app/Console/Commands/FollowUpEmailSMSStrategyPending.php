<?php
namespace App\Console\Commands;
use App\Repositories\UserRepository;

class FollowUpEmailSMSStrategyPending extends \Illuminate\Console\Command{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Api:FollowUpEmailSMSStrategyPending';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Followup Email-SMS strategy for Pending Users';

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
        $getUserRepository->getFollowUpUserDetailsS1Pending();
    }

}