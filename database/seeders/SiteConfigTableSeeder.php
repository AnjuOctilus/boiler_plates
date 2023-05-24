<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SiteConfig;

class SiteConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('site_config')->delete();
        $site_config_array = array (
        
            array(1,"QUEUE_STATUS","TRUE","0"),
            array(2,"QUEUE_FAIL_COUNT","0",NULL)
            
        );
        foreach($site_config_array as $key => $value) {
            SiteConfig::create([
                'id'           => $value[0],
                'config_title' => $value[1],
                'config_value' => $value[2],
                'config_info' => $value[3]
            ]);
        }
    }
}
