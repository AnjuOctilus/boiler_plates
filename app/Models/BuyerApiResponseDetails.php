<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class BuyerApiResponseDetails
 *
 * @package App\Models
 */
class BuyerApiResponseDetails extends Model
{
    /**
     * he table associated with the model.
     *
     * @var string
     */
    protected $table = 'buyer_api_response_details';
    /**
     *  The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['buyer_api_response_id', 'post_param', 'lead_value', 'status', 'created_at', 'updated_at'];
}
