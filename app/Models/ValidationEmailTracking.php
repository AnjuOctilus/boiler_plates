<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ValidationEmailTracking
 *
 * @package App\Models
 */
class ValidationEmailTracking extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'validation_email_tracking';
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
        'visitor_id', 'validated_email', 'result', 'result_details', 'validated_date'];
}
