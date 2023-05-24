<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdfTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_triggers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->default(NULL);
            $table->string('sale_status')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->tinyInteger('qualify_status')->nullable()->default('0');
            $table->tinyInteger('trigger_type')->nullable()->default(NULL);
            $table->tinyInteger('status')->default('0');
            $table->tinyInteger('post_crm')->nullable()->default(NULL);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['user_id'],'pdf_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdf_triggers');
    }
}
