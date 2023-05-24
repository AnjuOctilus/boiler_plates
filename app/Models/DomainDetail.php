<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DomainDetail
 *
 * @package App\Models
 */
class DomainDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'domain_name', 'type', 'status', 'last_active_date',
    ];

    /**
     * Adv info
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function adv_info()
    {
        return $this->hasOne(App\AdtopiaVisitor::class, 'visitor_id', 'id');
    }
}
