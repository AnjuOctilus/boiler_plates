<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvVisitorsLastVisit
 *
 * @package App\Models
 */
class AdvVisitorsLastVisit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_visitors_last_visit';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'adv_visitor_id', 'last_visit_page',
    ];
}
