<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class MiddlemanQUestionaireOption
 *
 * @package App\Models
 */
class MiddlemanQUestionaireOption extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'middleman_questionnaire_options';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['questionnaire_id', 'option_label', 'option_value', 'option_target', 'live_id', 'crm_id', 'status'];
}
