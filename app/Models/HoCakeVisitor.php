<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class HoCakeVisitor
 *
 * @package App\Models
 */
class HoCakeVisitor extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ho_cake_visitors';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'visitor_id', 'aff_id', 'aff_sub', 'aff_sub2',
        'aff_sub3', 'aff_sub4', 'aff_sub5', 'offer_id', 'aff_unique1', 'aff_unique2', 'aff_unique3', 'subid1', 'subid2', 'subid3'
    ];
}
