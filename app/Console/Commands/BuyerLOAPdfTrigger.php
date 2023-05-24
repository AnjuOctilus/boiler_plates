<?php

namespace App\Console\Commands;

use App\Models\CronMapping;
use App\Models\PdfTrigger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\BuyerCronPdfTrigger as PdfUpdateJob;
use App\Repositories\UserRepository;


class BuyerLOAPdfTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Api:BuyerLOAPdfTrigger';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Update LOA pdf data';

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
        $crons = CronMapping::where('name', '=', 'BuyerLOAPdfTrigger')
            ->where('status', '=', '1')
            ->first();
        if (!empty($crons)) {
            $userData = self::getUserData();
            if ($userData) {
                dispatch(new PdfUpdateJob($userData->id));
            } else {
                echo "no data found";
            }
        }
    }

    /**
     * Get user data
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUserData()
    {
        //$startDate = date('Y-m-d', strtotime("-7 days"));
        //$endDate = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        $users = DB::table('users')
            ->leftJoin('user_extra_details as UED', 'UED.user_id', '=', 'users.id')
            ->where('users.is_qualified', '=', 1)
            ->where('UED.complete_status', '=', 1)
            //->whereBetween('users.updated_at', array($startDate, $endDate))
            ->select('users.id')
            ->orderBy('users.updated_at', 'desc');

        $db_data = $users->first();
        return $db_data;
    }
}
