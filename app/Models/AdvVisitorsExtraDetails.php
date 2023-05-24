<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvVisitorsExtraDetails
 *
 * @package App\Models
 */
class AdvVisitorsExtraDetails extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adv_visitors_extra_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['adv_visitor_id', 'ext_var1', 'ext_var2', 'ext_var3', 'ext_var4', 'ext_var5'];
}
