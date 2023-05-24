<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrackerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tracker_masters')->delete();
        $tracker_array = array
        (
            array(1,"ADTOPIA"),
            array(2,"HO"),
            array(3,"THRIVE"),
            array(4,"FB"),
            array(5,"GDT"),
            array(6,"DIRECT"),
            array(7,"UN_KNOWN"),
        );

        foreach ($tracker_array as $key => $value) {
            DB::table('tracker_masters')->insert([
                'id' => $value[0],
                'tracker_name' => $value[1]
            ]);
        }
    }
}
