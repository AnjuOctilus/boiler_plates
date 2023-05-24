<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvVisitorsAdtopiaDetail
 *
 * @package App\Models
 */
class AdvVisitorsAdtopiaDetail extends Model
{
    protected $fillable = [
        'adv_visitor_id', 'atp_source', 'atp_vendor', 'atp_sub1', 'atp_sub2', 'atp_sub3', 'pid', 'acid', 'cid', 'crvid',
    ];

    public $timestamps 	= false;

    /**
     * Adv visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adv_visitor()
    {
        return $this->belongsTo(AdvVisitor::class,'adv_visitor_id','id');
    }
}
