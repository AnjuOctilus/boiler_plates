<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvHoDetail
 *
 * @package App\Models
 */
class AdvHoDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'adv_visitor_id', 'aff_id', 'aff_sub', 'aff_sub2', 'aff_sub3', 'aff_sub4', 'aff_sub5', 'campaign_id', 'offer_id', 'fb_source',
    ];
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Adv visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adv_visitor()
    {
        return $this->belongsTo(App\AdvVisitor::class, 'adv_visitor_id', 'id');
    }

}
