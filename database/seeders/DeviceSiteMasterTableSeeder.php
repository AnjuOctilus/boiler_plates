<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSiteMasterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('device_site_masters')->delete();

        $data_array = array
        (
            array(1,"All",'All pages'),
            array(2,"Web",'Website Page'),
            array(3,"Mobile","Mobile page"),
            array(4,"Tablet",'Tablet page')
        );
        foreach ($data_array as $key => $value)
        {
            DB::table('device_site_masters')->insert([
                'id' => $value[0],
                'device_site_name' => $value[1] ,
                'device_site_comment' => $value[2] ,
            ]);
        }
    }
}
