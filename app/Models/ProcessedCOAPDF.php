<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedCOAPdf extends Model{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'processed_coa_pdfs';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['lead_docs_id','user_id','created_at','updated_at'];
}