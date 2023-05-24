<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvVisitor
 *
 * @package App\Models
 */
class AdvVisitor extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'domain_id', 'adv_id', 'tracker_id', 'page_id', 'device_site_id', 'tracker_unique_id', 'sub_tracker', 'existingdomain', 'redirect_url', 'remote_ip', 'browser', 'os', 'country', 'timespent', 'device_type', 'resolution', 'user_agent', 'referer_site',
    ];

    /**
     * Adv adtopia detail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function adv_adtopia_detail()
    {
        return $this->hasOne(App\AdvAdtopiaDetail::class, 'adv_visitor_id', 'id');
    }

    /**
     * Adv extra detail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function adv_extra_detail()
    {
        return $this->hasOne(App\AdvExtraDetail::class, 'adv_visitor_id', 'id');
    }

    /**
     * Adv ho details
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function Adv_ho_detail()
    {
        return $this->hasOne(App\AdvHoDetail::class, 'adv_visitor_id', 'id');
    }
}
