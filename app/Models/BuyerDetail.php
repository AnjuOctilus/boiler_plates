<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class BuyerDetail
 *
 * @package App\Models
 */
class BuyerDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string[]
     */
    protected $fillable = ['buyer_name', 'data_key', 'status'];
}
