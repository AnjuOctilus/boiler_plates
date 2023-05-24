<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bankData = [
            ['id'=>1,'bank_code'=>NULL,'bank_name'=>'Lender-1','type' =>NULL],
            ['id'=>2,'bank_code'=>NULL,'bank_name'=>'Lender-2','type' =>NULL],
            ['id'=>3,'bank_code'=>NULL,'bank_name'=>'Lender-3','type' =>NULL],
            ['id'=>4,'bank_code'=>NULL,'bank_name'=>'Lender-4','type' =>NULL],
            ['id'=>5,'bank_code'=>NULL,'bank_name'=>'Lender-5','type' =>NULL],
            ['id'=>6,'bank_code'=>NULL,'bank_name'=>'Lender-6','type' =>NULL],
            ['id'=>7,'bank_code'=>NULL,'bank_name'=>'Lender-7','type' =>NULL],
        ]; 

        foreach ($bankData as $bank){
            $bankObject = new Bank();
            $bankObject->id = $bank['id'];
            $bankObject->bank_code = $bank['bank_code'];
            $bankObject->bank_name = $bank['bank_name'];
            $bankObject->type = $bank['type'];
            $bankObject->save();
        }
    }
}
