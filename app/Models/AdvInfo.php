<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvInfo
 *
 * @package App\Models
 */
class AdvInfo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'domain_id', 'page_id', 'adv_name', 'adv_path', 'statusIndex', 'last_active_date',
    ];

    /**
     * Domain details
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain_detail()
    {
        return $this->belongsTo(DomainDetail::class, 'domain_id', 'id');
    }

    /**
     * Adv visitor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adv_visitor()
    {
        return $this->belongsTo(AdvVisitor::class, 'adv_visitor_id', 'id');
    }
}
