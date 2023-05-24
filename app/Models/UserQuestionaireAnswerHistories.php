<?php


namespace App\Models;

/**
 * Class UserQuestionaireAnswerHistories
 *
 * @package App\Models
 */
class UserQuestionaireAnswerHistories
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'bank_id', 'type', 'raw_data','source'];
}
