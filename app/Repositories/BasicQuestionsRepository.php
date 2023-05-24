<?php

namespace App\Repositories;

use \Illuminate\Support\Facades\Validator;
use App\Repositories\Interfaces\BasicQuestionsInterface;
use App\Models\QuestionnaireOptions;
use App\Models\Questionnaire;

/**
 * Class BasicQuestionsRepository
 *
 * @package App\Repositories
 */
class BasicQuestionsRepository implements BasicQuestionsInterface
{
    /**
     * Get question
     *
     * @param $request
     * @return array|string[]
     */
    public function getQuestion($request)
    {
        // $arrays[] =$request->questionId;
        if (empty($request->questionId)) {
            $data = array('response' => 'Question Id is empty', 'status' => 'Failed');
            return $data;
        }
        $question_id = array_map('intval', explode(',', $request->questionId));
        $count = count($question_id);

        for ($i = 0; $i < $count; $i++) {
            $questionaire_options = QuestionnaireOptions::where('questionnaire_id', $question_id[$i])->where('status', 1)->get();
            $questionaire_details = Questionnaire::where('id', $question_id[$i])->where('status', 1)->first();
            $questionaire_title = isset($questionaire_details) ? $questionaire_details->title : "";
            $questionaire_id = isset($questionaire_details) ? $question_id[$i] : "";
            $values = array();
            $option_id = array();
            $final_qn_ans = array();
            $answers = array();
            //  dd($questionaire_options);
            unset($answers);
            foreach ($questionaire_options as $opt_key) {
                $values = $opt_key->value;
                $option_id = $opt_key->id;
                $answers = ["label" => $values, "value" => $option_id];
                array_push($final_qn_ans, $answers);
            }

            $data[] = [
                "questionId" => $questionaire_id,
                "question" => $questionaire_title,
                "answers" => $final_qn_ans,

            ];

        }
        $data = ['response' => $data, 'status' => 'Success'];
        return $data;

    }


}
