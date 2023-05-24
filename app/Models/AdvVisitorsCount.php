<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvVisitorsCount
 *
 * @package App\Models
 */
class AdvVisitorsCount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_visitors_count';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['adv_visitor_id', 'counts', 'adv_id'];

}
