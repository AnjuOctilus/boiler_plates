<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_bank_id')->nullable();
            $table->integer('user_id');
            $table->string('phone',20)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('email',50)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->enum('type', ['sms', 'email'])->collate('utf8mb4_unicode_ci');
            $table->string('token',20)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->enum('is_signed', ['1', '0'])->collate('utf8mb4_unicode_ci')->default('0');
            $table->enum('questions', ['1', '0'])->collate('utf8mb4_unicode_ci')->default('0');
            $table->enum('bank_details', ['1', '0'])->collate('utf8mb4_unicode_ci')->default('0');
            $table->enum('status', ['1', '0'])->collate('utf8mb4_unicode_ci')->default('1');
            $table->timestamp('lead_date')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index(['user_bank_id','status','created_at'],'followup_list_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followup_list');
    }
}
