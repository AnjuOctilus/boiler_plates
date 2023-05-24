<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuyerDetailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('buyer_details')->delete();
        $buyer_details_array = array
        (
            array(1,"Cake"),
            array(2,"CRM"),

        );

        foreach ($buyer_details_array as $key => $value) {
            DB::table('buyer_details')->insert([
                'id' => $value[0],
                'buyer_name' => $value[1] ,
            ]);
        }
    }
}
