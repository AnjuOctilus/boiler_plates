<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ValidationPhoneTracking
 *
 * @package App\Models
 */
class ValidationPhoneTracking extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'validation_phone_tracking';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'phone_number', 'validation_type', 'validation_result', 'validation_result_details'];

}
