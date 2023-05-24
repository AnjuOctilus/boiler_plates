<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApiHistory
 *
 * @package App\Models
 */
class ApiHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_histories_new';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'user_uuid', 'url', 'buyer_api_id', 'request_type', 'request', 'request_type', 'status', 'response'];
}
