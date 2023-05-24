<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSqslPageVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sqsl_page_visits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('transid',191)->nullable();
            $table->string('affiliated_id',191)->nullable();
            $table->string('campaign_id',191)->nullable();
            $table->string('ip_address',191)->nullable();
            $table->dateTime('date_time')->nullable();
            $table->string('time_spent',191)->nullable();
            $table->string('browser',191)->nullable();
            $table->string('resolution',191)->nullable();
            $table->string('os',191)->nullable();
            $table->string('device',191)->nullable();
            $table->string('country',191)->nullable();
            $table->string('click_link',191)->nullable();
            $table->string('link_url',191)->nullable();
            $table->string('split_name',191)->nullable();
            $table->string('page',191)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('cascade');
            $table->index(['visitor_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sqsl_page_visits');
    }
}
