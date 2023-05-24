<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdtopiaVisitor
 *
 * @package App\Models
 */
class AdtopiaVisitor extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'atp_source', 'atp_vendor', 'atp_sub1', 'atp_sub2', 'atp_sub3', 'atp_sub4', 'atp_sub5', 'media_vendor'
    ];
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor()
    {
        return $this->belongsTo(App\Visitor::class, 'visitor_id');
    }
}
