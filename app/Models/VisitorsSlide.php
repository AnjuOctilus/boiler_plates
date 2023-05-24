<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class VisitorsSlide
 * 
 * @package App\Models
 */
class VisitorsSlide extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name', 'split_id', 'visitor_id', 'user_id'];
}
