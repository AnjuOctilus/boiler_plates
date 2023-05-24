<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bank_id')->nullable()->default(NULL);
            $table->enum('type', ['digital','wet'])->collate('utf8mb4_unicode_ci')->default('wet');
            $table->text('signature_image')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->text('pdf_file')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->text('s3_file_path')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->enum('status', ['1','0'])->collate('utf8mb4_unicode_ci')->default('1');
            $table->string('previous_name')->nullable()->default(NULL);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['user_id','bank_id','type','status','created_at'],'signature_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signatures');
    }
}
