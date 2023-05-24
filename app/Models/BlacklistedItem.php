<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class BlacklistedItem
 *
 * @package App\Models
 */
class BlacklistedItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string[]
     */
    protected $fillable = [
        'bi_value', 'bi_type',
    ];
}
