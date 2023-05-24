<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class FollowupList
 *
 * @package App\Models
 */
class FollowupList extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'followup_list';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'type', 'lead_date'];
}
