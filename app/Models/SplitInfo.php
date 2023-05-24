<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SplitInfo
 *
 * @package App\Models
 */
class SplitInfo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'split_info';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'domain_id', 'buyer_id', 'split_name', 'split_path', 'status', 'last_active_date',
    ];

    /**
     * Visitors extra details
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function visitors_extra_detail()
    {
        return $this->hasOne(VisitorsExtraDetail::class, 'split_id', 'id');
    }


}
