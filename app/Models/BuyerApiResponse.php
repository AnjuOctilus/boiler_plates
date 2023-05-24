<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class BuyerApiResponse
 *
 * @package App\Models
 */
class BuyerApiResponse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'buyer_api_responses';
    /**
     *  The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['buyer_id', 'user_id', 'bank_id', 'signature_id', 'result','buyer_request_type','api_response', 'lead_id', 'created_at', 'updated_at'];
}
