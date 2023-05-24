<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvPixelFiring
 *
 * @package App\Models
 */
class AdvPixelFiring extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_pixel_firing';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['adv_visitor_id', 'page_type'];
}
