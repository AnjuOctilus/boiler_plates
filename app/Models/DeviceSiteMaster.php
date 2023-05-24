<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceSiteMaster
 *
 * @package App\Models
 */
class DeviceSiteMaster extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'device_site_name', 'device_site_comment',
    ];
}
