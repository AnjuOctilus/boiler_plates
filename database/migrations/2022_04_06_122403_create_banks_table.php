<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bank_code',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('bank_name')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->integer('rank')->nullable()->default(NULL);
            $table->enum('sign_type', ['digital','wet'])->default('wet')->collate('utf8mb4_unicode_ci');
            $table->enum('status', ['1','0'])->default('1')->collate('utf8mb4_unicode_ci');
            $table->integer('type')->nullable()->default(NULL);
            $table->unsignedBigInteger('product_id');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['sign_type','status','product_id','created_at'],'bank_index');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banks');
    }
}
