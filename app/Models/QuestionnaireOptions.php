<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class QuestionnaireOptions
 *
 * @package App\Models
 */
class QuestionnaireOptions extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['questionnaire_id', 'value', 'status'];
}
