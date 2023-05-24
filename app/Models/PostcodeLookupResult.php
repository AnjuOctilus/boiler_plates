<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class PostcodeLookupResult
 *
 * @package App\Models
 */
class PostcodeLookupResult extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'postcode_lookup_result';
    /**
     *  The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['visitor_id', 'post_code', 'paf_id', 'credits_display_text', 'organisation', 'line1', 'line2',
        'line3', 'town', 'county', 'country', 'deliverypointsuffix', 'nohouseholds', 'smallorg', 'pobox', 'rawpostcode', 'pz_mailsort',
        'spare', 'udprn', 'fl_unique'];
}
