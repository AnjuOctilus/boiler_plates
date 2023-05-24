<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class TrackerMaster
 *
 * @package App\Models
 */
class TrackerMaster extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['tracker_name', 'tracker_comment'];
}
