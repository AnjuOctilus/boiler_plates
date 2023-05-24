<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Signature
 *
 * @package App\Models
 */
class Signature extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'signatures';
    /**
     * The table associated with the model.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'bank_id', 'type', 'signature_image', 'pdf_file', 's3_file_path', 'status', 'updated_at','previous_name'];
}
