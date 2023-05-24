<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FollowupHistories
 *
 * @package App\Models
 */
class FollowupHistories extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'type', 'type_id', 'value', 'source', 'post_crm'
    ];

    /**
     * Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function question()
    {
        return $this->hasOne('App\Questionnaire', 'id', 'type_id');
    }

    /**
     * Option
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany('App\QuestionnaireOptions', 'id', 'value');
    }
}
