<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VisitorsJourney
 *
 * @package App\Models
 */
class VisitorsJourney extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visitors_journey';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'user_id', 'page_type',
    ];

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



