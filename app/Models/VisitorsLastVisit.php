<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VisitorsLastVisit
 *
 * @package App\Models
 */
class VisitorsLastVisit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visitors_last_visit';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'last_visit_page',
    ];

    /**
     * Visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor()
    {
        return $this->belongsTo(Visitor::class, 'visitor_id');
    }


}
