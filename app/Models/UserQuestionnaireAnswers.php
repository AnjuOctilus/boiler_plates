<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class UserQuestionaireAnswers
 *
 * @package App\Models
 */
class UserQuestionnaireAnswers extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_questionnaire_answers';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'bank_id', 'questionnaire_id', 'questionnaire_option_id', 'input_answer', 'status'];
}
