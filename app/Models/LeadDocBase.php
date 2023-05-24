<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class LeadDoc
 *
 * @package App\Models
 */
class LeadDocBase extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_docs_base';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id','pdf_file_base','bank_loa_pdf_files_base','questionnaire_pdf_files_base','witness_statement_pdf_base',
        'statement_of_truth_pdf_base','sra_pdf_base','created_at','updated_at'
    ];
}
