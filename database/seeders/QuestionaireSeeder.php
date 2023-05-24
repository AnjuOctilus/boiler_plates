<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $questions = [
            [
                'id'=>1,
                'title' => 'Are you in an IVA or currently Bankrupt ?',
                'is_required' => 'yes',
                'type' => 'questionnaire0',
                'form_type' => 'button',
                'default_id' => 1,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>2,
                'title' => 'Did you take out a loan, mortgage or credit card after 2008 ?',
                'is_required' => 'yes',
                'type' => 'questionnaire0',
                'form_type' => 'button',
                'default_id' => 2,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>3,
                'title' => 'Have you ever had any Loans, Credit Cards, Mortgages, Store Cards, or Car Loans?',
                'is_required' => 'yes',
                'type' => 'questionnaire0',
                'form_type' => 'button',
                'default_id' => 3,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>4,
                'title' => 'Do you still have the original paperwork for all of these loans?',
                'is_required' => 'yes',
                'type' => 'questionnaire0',
                'form_type' => 'button',
                'default_id' => 4,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>5,
                'title' => 'Have you previously looked into PPI?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 5,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>6,
                'title' => 'Did you receive a refund?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 6,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>7,
                'title' => 'Was the refund in question received prior to 2017?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 7,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>8,
                'title' => 'Was your claim rejected?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 8,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>9,
                'title' => 'Are you aware if the PPI policy is still active on your credit facility?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 9,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>10,
                'title' => 'Have you ever been made unemployed and if so, did you maintain payments to the lender?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 10,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>11,
                'title' => 'Have you ever claimed on your policy for accident, sickness or unemployment?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 11,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>12,
                'title' => 'Do you have any reason to believe the lender may have acted irresponsibly?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 12,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>13,
                'title' => 'At the point of obtaining the credit, did you understand all the key risks and negative consequences posed within the credit agreement?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 12,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>14,
                'title' => 'Have you ever struggled to keep up with repayments on your credit agreement?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 12,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
            [
                'id'=>15,
                'title' => 'Has the policy been jointly held for any of the lenders you have selected?',
                'is_required' => 'yes',
                'type' => 'questionnaire1',
                'form_type' => 'button',
                'default_id' => 12,
                'parent' => 0,
                'extra_param' => null,
                'status' => 1
            ],
        ];
        DB::table( 'questionnaires' )->delete();
        DB::table( 'questionnaires' )->insert( $questions );
    }
}
