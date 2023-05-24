<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLeadDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_docs', function (Blueprint $table) {           
            $table->string('coa_pdf_files')->after('bank_loa_pdf_files')->nullable();
            $table->string('questionniare_pdf_files')->after('coa_pdf_files')->nullable();
            $table->string('statement_pdf_files')->after('questionniare_pdf_files')->nullable();
            $table->string('preview_pdf_files')->after('statement_pdf_files')->nullable();          
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_docs', function (Blueprint $table) {
            $table->dropColumn('coa_pdf_files');
            $table->dropColumn('questionniare_pdf_files');
            $table->dropColumn('statement_pdf_files');
            $table->dropColumn('preview_pdf_files');
        });
    }
}
