<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class MiddlemanQuestionaire
 *
 * @package App\Models
 */
class MiddlemanQuestionaire extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'middleman_questionnaires';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['question_title', 'form_type', 'parent_id', 'live_id', 'crm_id', 'status'];
}
