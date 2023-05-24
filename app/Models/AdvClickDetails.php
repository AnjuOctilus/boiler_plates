<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvClickDetails
 *
 * @package App\Models
 */
class AdvClickDetails extends Model
{
    /**
     *  The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_click_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['adv_visitor_id', 'adv_id', 'remote_ip',
        'date_time', 'time_spent', 'resolution', 'click_link', 'link_url', 'page'];
}
