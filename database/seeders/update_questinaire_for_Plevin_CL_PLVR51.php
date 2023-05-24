<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class update_questinaire_for_Plevin_CL_PLVR51 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $questions = [
           [21,132,'Is this your Vehicle?','yes','questionnaire0','button',1,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [22,132,'Did you secure finance for this vehicle in 2020?\n                ','yes','questionnaire0','button',2,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [23,132,'When did you secure finance for this vehicle?\n                ','yes','questionnaire0','button',3,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [24,132,'Approximate amount of the finance agreement?\n                ','yes','questionnaire0','button',4,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [25,132,'Is your finance with Blackhorse still in place?\r\n                ','yes','questionnaire0','button',5,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [26,132,'Do you still have this vehicle?\n                ','yes','questionnaire0','button',6,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [27,132,'What happened to the vehicle?\n                ','yes','questionnaire0','button',7,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [28,132,'What is your preference on what happens to this vehicle?\n                ','yes','questionnaire0','button',8,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [29,132,'Did Blackhorse check you had enough disposible income to afford the monthly repayments of the new finance agreement?\r\n                ','yes','questionnaire0','button',9,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [30,132,'Approximately what was your average or usual monthly income(after tax) when your finance agreement began?\n                ','yes','questionnaire0','button',10,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [31,132,'When your finance agreement began, approximately how much per month were you typically paying towards other credit commitments(loans and card accounts, etc)\n                ','yes','questionnaire0','button',11,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [32,132,'When my finance agreement began, I was…\n                ','yes','questionnaire0','button',12,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [33,132,'When the finance agreement began, were these things typically happening in your bank account?\n                ','yes','questionnaire0','button',13,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [34,132,'Are you subject to or have you ever been subject to an Individual Voluntary Arrangement(IVA) or have you proposed an IVA yet to be approved or rejected by creditors?\n                ','yes','questionnaire0','button',14,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [35,132,'Do any of these scenarios apply to you?\n                ','yes','questionnaire0','button',15,0,NULL,'1','2023-01-25 18:22:31','2023-01-25 18:22:31'],
           [36,132,'Did you arrange finance via the dealer or a broker?','yes','questionnaire0','button',16,0,NULL,'1','2023-02-17 13:23:41','2023-02-17 13:23:41'],
           [37,132,'Would you like us to investigate and, where appropriate, claim for unfair/hidden commission inappropriately charged to you?','yes','questionnaire0','button',17,0,NULL,'1','2023-02-17 13:23:41','2023-02-17 13:23:41'],
           [38,132,'What is your finance agreement number?','yes','questionnaire1','button',18,NULL,NULL,'1','2023-02-24 17:34:54','2023-02-24 17:34:54'],

        ];
        DB::table( 'questionnaires' )->insert( $questions );
    }
}
