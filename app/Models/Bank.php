<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Bank
 *
 * @package App\Models
 */
class Bank extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string[]
     */
    protected $fillable = [
        'id','bank_code', 'bank_name', 'rank', 'sign_type', 'status',
    ];
}
