<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_docs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tax_payer')->nullable();
            $table->string('user_insurance_number')->nullable();
            $table->string('spouses_insurance_number')->nullable();
            $table->string('user_identification_type')->nullable();
            $table->text('user_identification_image')->nullable();
            $table->string('user_identification_image_s3')->nullable();
            $table->string('spouses_identification_type')->nullable();
            $table->text('spouses_identification_image')->nullable();
            $table->string('spouses_identification_image_s3')->nullable();
            $table->string('terms_file')->nullable();
            $table->string('cover_page')->nullable();
            $table->string('pdf_file')->nullable();
            $table->text('bank_loa_pdf_files')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id','tax_payer','created_at'],'ld_indx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_docs');
    }
}
