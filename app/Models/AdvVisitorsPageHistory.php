<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvVisitorsPageHistory
 *
 * @package App\Models
 */
class AdvVisitorsPageHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_visitors_page_history';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['adv_visitor_id', 'last_visit_page'];
}
