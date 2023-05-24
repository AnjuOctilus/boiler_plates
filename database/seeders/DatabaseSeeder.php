<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {        
         $this->call(BankSeeder::class);
         $this->call(BuyerDetailsTableSeeder::class);
         $this->call(DeviceSiteMasterTableSeeder::class);
         $this->call(TrackerTableSeeder::class);
         $this->call(QuestionaireSeeder::class);
         $this->call(QuestionaireOptionSeeder::class);
         $this->call(SiteConfigTableSeeder::class);
         $this->call(CronSeeder::class);
         $this->call(FollowupStrategyContentSeeder::class);
    }
}
