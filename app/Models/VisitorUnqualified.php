<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class VisitorUnqualified
 *
 * @package App\Models
 */
class VisitorUnqualified extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['visitor_id', 'question_id', 'answer_id','input_answer'];
}
