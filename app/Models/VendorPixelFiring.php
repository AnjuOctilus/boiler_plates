<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VendorPixelFiring
 *
 * @package App\Models
 */
class VendorPixelFiring extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vendor_pixel_firing';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'user_id', 'vendor', 'page_type', 'pixel_type', 'pixel_log', 'pixel_result'
    ];
}
