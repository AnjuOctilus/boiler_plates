<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvExtraDetail
 *
 * @package App\Models
 */
class AdvExtraDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'adv_visitor_id', 'adv_id', 'ext_var1', 'ext_var2', 'ext_var3', 'ext_var4', 'ext_var5',
    ];

    /**
     * Visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor()
    {
        return $this->belongsTo(App\Visitor::class, 'visitor_id', 'id');
    }

    /**
     * Adv visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adv_visitor()
    {
        return $this->belongsTo(App\AdvVisitor::class, 'adv_visitor_id', 'id');
    }

    /**
     * Adv info
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adv_info()
    {
        return $this->belongsTo(App\AdvInfo::class, 'adv_id', 'id');
    }
}
