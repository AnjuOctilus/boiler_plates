<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionaireOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $questionnaireOptions = [
            [
                'id' => 1,
                'questionnaire_id' => 1,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '1',
                'default_id' => '1'

            ],
            [
                'id' => 2,
                'questionnaire_id' => 1,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '2'
            ],
            [
                'id' => 3,
                'questionnaire_id' => 2,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '1',
                'default_id' => '3'

            ],
            [
                'id' => 4,
                'questionnaire_id' => 2,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '4'

            ],
            [
                'id' => 5,
                'questionnaire_id' => 3,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '5'

            ],
            [
                'id' => 6,
                'questionnaire_id' => 3,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '6'

            ],
            [
                'id' => 7,
                'questionnaire_id' => 4,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '7'

            ],
            [
                'id' => 8,
                'questionnaire_id' => 4,
                'label' => '<span>Some</span>',
                'value' => 'Some',
                'target' => '',
                'rank' => '2',
                'default_id' => '8'

            ],
            [
                'id' => 9,
                'questionnaire_id' => 4,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '9'

            ],
            

            [
                'id' => 10,
                'questionnaire_id' => 5,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '10'

            ],
            [
                'id' => 11,
                'questionnaire_id' => 5,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '11'

            ],
            [
                'id' => 12,
                'questionnaire_id' => 6,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '12'

            ],
            [
                'id' => 13,
                'questionnaire_id' => 6,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '13'

            ],
            [
                'id' => 14,
                'questionnaire_id' => 7,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '14'

            ],
            [
                'id' => 15,
                'questionnaire_id' => 7,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '15'

            ],
            [
                'id' => 16,
                'questionnaire_id' => 8,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '16'

            ],
            [
                'id' => 17,
                'questionnaire_id' => 8,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '17'

            ],
            [
                'id' => 18,
                'questionnaire_id' => 9,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '18'

            ],
            [
                'id' => 19,
                'questionnaire_id' => 9,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '19'

            ],
            [
                'id' => 20,
                'questionnaire_id' => 10,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '20'

            ],
            [
                'id' => 21,
                'questionnaire_id' => 10,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '21'

            ],
            [
                'id' => 22,
                'questionnaire_id' => 11,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '22'

            ],
            [
                'id' => 23,
                'questionnaire_id' => 11,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '23'

            ],
            [
                'id' => 24,
                'questionnaire_id' => 12,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '24'

            ],
            [
                'id' => 25,
                'questionnaire_id' => 12,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '25'

            ],
            [
                'id' => 26,
                'questionnaire_id' => 13,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '26'

            ],
            [
                'id' => 27,
                'questionnaire_id' => 13,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '27'

            ],
            [
                'id' => 28,
                'questionnaire_id' => 14,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '28'

            ],
            [
                'id' => 29,
                'questionnaire_id' => 14,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '29'

            ],
            [
                'id' => 30,
                'questionnaire_id' => 15,
                'label' => '<span>Yes</span>',
                'value' => 'Yes',
                'target' => '',
                'rank' => '2',
                'default_id' => '30'

            ],
            [
                'id' => 31,
                'questionnaire_id' => 15,
                'label' => '<span>No</span>',
                'value' => 'No',
                'target' => '',
                'rank' => '2',
                'default_id' => '31'

            ],
            
        ];
        DB::table( 'questionnaire_options' )->delete();
        DB::table( 'questionnaire_options' )->insert( $questionnaireOptions );
    }
}
