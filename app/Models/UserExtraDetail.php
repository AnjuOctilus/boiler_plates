<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class UserExtraDetail
 *
 * @package App\Models
 */
class UserExtraDetail extends Model
{
    /**
     *  The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_extra_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'previous_name','is_pixel_fire', 'is_fb_pixel_fired', 'pixel_log', 'pixel_type', 'qualify_status', 'agree_terms', 'complete_status','previous_address','previous_post_code'
    ];
}
