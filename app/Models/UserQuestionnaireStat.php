<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserQuestionnaireStat
 *
 * @package App\Models
 */
class UserQuestionnaireStat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'bank_id', 'questionnaire_id', 'source'
    ];
}
