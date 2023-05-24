<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class PostcodelookupSearch
 *
 * @package App\Models
 */
class PostcodelookupSearch extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'postcode_lookup_search';
    /**
     *  The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['visitor_id', 'post_code', 'paf_id', 'address'];
}
