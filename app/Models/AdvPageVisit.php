<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvPageVisit
 *
 * @package App\Models
 */
class AdvPageVisit extends Model
{
    /**
     *The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_page_visits';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'adv_visitor_id', 'transid', 'affiliated_id', 'campaign_id', 'ip_address', 'date_time', 'time_spent', 'resolution', 'click_link', 'split_name', 'page'
    ];

}
