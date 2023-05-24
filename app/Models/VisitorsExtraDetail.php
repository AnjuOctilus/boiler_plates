<?php

namespace App\Models;

use App\Models\Visitor;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VisitorsExtraDetail
 *
 * @package App\Models
 */
class VisitorsExtraDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'split_id', 'ext_var1', 'ext_var2', 'ext_var3', 'ext_var4', 'ext_var5',
    ];

    /**
     * Split info
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function split_info()
    {
        return $this->belongsTo(SplitInfo::class, 'split_id');
    }

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
