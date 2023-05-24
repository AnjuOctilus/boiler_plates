<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FollowupVisit
 *
 * @package App\Models
 */
class FollowupVisit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'followup_visit';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['atp_sub2', 'user_id', 'visitor_id', 'tracker_unique_id', 'request', 'fireflag', 'adtopia_response', 'type', 'source'];

}
