<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Questionnaire
 *
 * @package App\Models
 */
class Questionnaire extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['bank_id', 'title', 'is_required', 'type', 'form_type', 'status'];

    /**
     * Options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(QuestionnaireOptions::class, 'questionnaire_id', 'id');

    }
}
