<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserQuestionnaireAnswersHistories
 *
 * @package App\Models
 */
class UserQuestionnaireAnswersHistories extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'bank_id', 'type', 'raw_data', 'source'
    ];

    //
}
