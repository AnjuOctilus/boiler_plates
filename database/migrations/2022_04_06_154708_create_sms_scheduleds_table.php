<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsScheduledsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_scheduleds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('domain_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('atp_url_id')->nullable();
            $table->text('sms_batch_id')->nullable();
            $table->text('email_batch_id')->nullable();
            $table->dateTime('scheduled_date')->nullable();
            $table->string('status',191)->nullable();
            $table->enum('type', ['SMS', 'Email'])->default(NULL);
            $table->text('response')->nullable();
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
        Schema::dropIfExists('sms_scheduleds');
    }
}
