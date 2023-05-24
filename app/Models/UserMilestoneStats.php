<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserMilestoneStats
 *
 * @package App\Models
 */
class UserMilestoneStats extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_milestone_stats';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'user_signature', 'partner_signature', 'questions', 'account_number', 'sort_code', 'sale', 'source', 'user_completed', 'user_completed_date', 'partner_completed', 'partner_completed_date', 'completed', 'completed_date', 'agree_terms'
    ];
}
