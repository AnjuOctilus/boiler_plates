<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CronSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cron_mappings')->delete();
        $cron_mappings_array = array
        (
            array(1,"BuyerLOAPdfTrigger"),
            array(2,"FollowUpSmsStrategyCron"),
            array(3,"FollowUpEmailStrategyCron"),
            array(4,"FollowUpHistoricalEmailStrategyCron"),
            array(5,"FollowUpHistoricalSmsStrategyCron"),
            array(6,"PostingCompletedLeadStrategy"),
            array(7,"RePdfGenerationCron"),
            array(8,"PostingHistoryCompletedLeadStrategy"),
            array(9,"FollowUpSmsStrategy24HrsCron"),
            array(10,"FollowUpEmailStrategy24HrsCron"),
            array(11,"FollowUpSmsStrategy48HrsCron"),
            array(12,"FollowUpSmsStrategyCronNew"),
            array(13,"FollowUpEmailStrategyCronNew"),
            array(14,"FollowUpSmsStrategy24HrsCronNew"),
            array(15,"FollowUpEmailStrategy24HrsCronNew"),
            array(16,"FollowUpSmsStrategy48HrsCronNew"),

        );

        foreach ($cron_mappings_array as $key => $value) {
            DB::table('cron_mappings')->insert([
                'id' => $value[0],
                'name' => $value[1] ,
                'status' => 1
            ]);
        }
    }
}
