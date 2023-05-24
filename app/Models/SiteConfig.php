<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class SiteConfig
 *
 * @package App\Models
 */
class SiteConfig extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     *  The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_config';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['config_title', 'config_value', 'config_info'];
}
