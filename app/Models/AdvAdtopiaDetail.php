<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvAdtopiaDetail
 *
 * @package App\Models
 */
class AdvAdtopiaDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_visitors_adtopia_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'adv_visitor_id', 'atp_source', 'atp_vendor', 'atp_sub1', 'atp_sub2', 'atp_sub3', 'pid', 'acid', 'cid', 'crvid',
    ];

    public $timestamps = false;

    /**
     * Adv visitors
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adv_visitor()
    {
        return $this->belongsTo(App\AdvVisitor::class, 'adv_visitor_id', 'id');
    }
}
