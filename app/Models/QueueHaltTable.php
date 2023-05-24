<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class QueueHaltTable
 *
 * @package App\Models
 */
class QueueHaltTable extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'queue_halt_table';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'visitor_id', 'status'];
}
