<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvVisitorsTemp
 *
 * @package App\Models
 */
class AdvVisitorsTemp extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_visitors_temp';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['adv_visitor_id', 'adv_id', 'tracker_id', 'device_site_id', 'tracker_unique_id', 'remote_ip',
        'browser', 'os', 'country', 'device_type'];
}
