<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSpousesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_spouses_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->default(NULL);
            $table->string('spouses_title',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('spouses_first_name',191)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('spouses_last_name',191)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->date('dob')->nullable();
            $table->date('date_of_marriage')->nullable();
            $table->text('signature')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->enum('status', ['1','0'])->default('1');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->index(['user_id'],'user_spouses_details_index');

                   });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_spouses_details');
    }
}
