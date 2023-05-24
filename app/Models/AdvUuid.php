<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvUuid
 *
 * @package App\Models
 */
class AdvUuid extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_uuid';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['adv_visitor_id', 'uuid'];
}
