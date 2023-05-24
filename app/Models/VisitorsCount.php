<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VisitorsCount
 *
 * @package App\Models
 */
class VisitorsCount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visitors_count';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'count', 'split_id',
    ];

    /**
     * Split info
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function split_info()
    {
        return $this->belongsTo(App\SplitInfo::class, 'split_id');
    }

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
