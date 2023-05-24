<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class LeadDoc
 *
 * @package App\Models
 */
class LeadDoc extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_docs';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'tax_payer', 'user_insurance_number', 'spouses_insurance_number',
        'user_identification_type', 'user_identification_image','user_identification_image_s3',
         'spouses_identification_type', 'spouses_identification_image', 'bank_loa_pdf_files', 
         'pdf_file', 'terms_file', 'cover_page'
        ,'coa_pdf_files','statement_of_truth_pdf','questionnaire_pdf_files','witness_statement_pdf'
    ];
}
