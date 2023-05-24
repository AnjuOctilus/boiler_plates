<?php

namespace App\Console\Commands;
use App\Models\PdfTrigger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use App\Models\CronMapping;
use App\Jobs\UpdateAddressDetails;




class AddressDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Api:AddressDetails';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Update user address details';

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
     * @return mixed
     */
    public function handle()
    {
        $crons = CronMapping::where('name', '=', 'AddressDetails')
        ->where('status', '=', 1)
        ->first();
        if (!empty($crons)) {
            
            $start = 500;
            $end = 600;
           $limit = 5;
            $userId = DB::table('user_crone_processed')->select('user_id')->orderBy('id','desc')->first();
            if(!empty($userId))
            {
                $start = $userId->user_id;
            }
            dispatch(new UpdateAddressDetails($limit,$start,$end));
           
           
        }
        
      
    }

   
}
