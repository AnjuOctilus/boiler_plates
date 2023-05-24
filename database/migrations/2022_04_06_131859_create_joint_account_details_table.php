<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJointAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('joint_account_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('bank_id');
            $table->string('first_name',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('last_name',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('dob',50)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('joint_account_details');
    }
}
