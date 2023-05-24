<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserQuestionnaireMeta
 *
 * @package App\Models
 */
class UserQuestionnaireMeta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_questionnaire_meta';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'bank_id', 'version', 'status'
    ];
}
