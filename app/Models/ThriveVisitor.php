<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class ThriveVisitor
 *
 * @package App\Models
 */
class ThriveVisitor extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'thr_source', 'thr_sub1', 'thr_sub2', 'thr_sub3', 'thr_sub4', 'thr_sub5', 'thr_sub6', 'thr_sub7', 'thr_sub8', 'thr_sub9', 'thr_sub10',
    ];
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Visitors
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor()
    {
        return $this->belongsTo(Visitor::class, 'visitor_id');
    }
}
