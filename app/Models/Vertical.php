<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Vertical
 *
 * @package App\Models
 */
class Vertical extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['vertical_name', 'vertical_comment'];
}
