<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class UserBankDetail
 *
 * @package App\Models
 */
class UserBankDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_banks';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'bank_id', 'is_joint'
    ];
}
