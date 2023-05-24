<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Share
 *
 * @package App\Models
 */
class Share extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shares';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'is_share'];
}
