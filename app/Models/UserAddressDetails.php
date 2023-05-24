<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAddressDetails
 *
 * @package App\Models
 */
class UserAddressDetails extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_address_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'address_type', 'postcode', 'address_line1', 'address_line2', 'address_line3', 'address_line4', 'town', 'locality',
        'county', 'district', 'vendor', 'address_id', 'is_manual', 'approve_status','previous_address', 'created_at', 'updated_at'
    ];
}
