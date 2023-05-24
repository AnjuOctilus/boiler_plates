<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class entryToCronMapping extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cron_mappings')->insert([
            'id' =>19,
            'name' => 'AddressDetails' ,
            'status' => 1
        ]);
    }
}
