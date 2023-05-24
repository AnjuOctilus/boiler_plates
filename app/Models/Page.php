<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Page
 *
 * @package App\Models
 */
class Page extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string[]
     */
    protected $fillable = ['vertical_id', 'cake_vertical_id', 'page', 'page_label'];

}
