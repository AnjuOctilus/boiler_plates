<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class FollowupStrategyContent
 *
 * @package App\Models
 */
class FollowupStrategyContent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'followup_strategy_content';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['id', 'template_id', 'conent'];
}
