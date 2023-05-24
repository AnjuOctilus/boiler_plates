<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Affliate
 *
 * @package App\Models
 */
class Affliate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'affliate';
    /**
     *  The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'affiliate_name', 'conversion_pixel', 'pixel_callback', 'pixel_type', 'percentage_type', 'tracking_percentage', 'mon_tracking', 'tue_tracking', 'wed_tracking', 'thu_tracking', 'fri_tracking', 'sat_tracking', 'sun_tracking', 'weightage_percentage', 'mon_weightage', 'tue_weightage', 'wed_weightage', 'thu_weightage', 'fri_weightage', 'sat_weightage', 'sun_weightage', 'time_zone', 'tracking_days', 'max_pixel_per_day', 'time_of_day', 'tracking_counter', 'tracking_batch', 'active', 'site_flag_id', 'ho_offer_id',
    ];
}
