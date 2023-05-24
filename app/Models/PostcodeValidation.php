<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class PostcodeValidation
 *
 * @package App\Models
 */
class PostcodeValidation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'postcode_validation';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['visitor_id', 'post_code', 'address', 'credits_display_text', 'lookup_id', 'organisation', 'line1', 'line2',
        'line3', 'town', 'county', 'country', 'deliverypointsuffix', 'nohouseholds', 'smallorg', 'pobox', 'rawpostcode', 'pz_mailsort',
        'spare', 'udprn', 'fl_unique', 'status'];
}
