<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FollowupVendorPixelFiring
 *
 * @package App\Models
 */
class FollowupVendorPixelFiring extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'followup_vendor_pixel_firing';
    /**
     *  The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['followup_visit_id', 'visitor_id', 'user_id', 'vendor', 'page_type', 'pixel_type', 'pixel_log'];
}
