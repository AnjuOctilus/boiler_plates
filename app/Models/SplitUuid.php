<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class SplitUuid
 *
 * @package App\Models
 */
class SplitUuid extends Model
{
    /**
     * Table
     *
     * @var string
     */
    protected $table = 'split_uuid';
    /**
     * Fields
     *
     * @var string[]
     */
    protected $fillable = ['visitor_id', 'uuid'];
}
