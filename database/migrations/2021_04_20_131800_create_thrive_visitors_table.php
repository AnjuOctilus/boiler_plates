<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThriveVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thrive_visitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('thr_source',150)->nullable();
            $table->string('thr_sub1',150)->nullable();
            $table->string('thr_sub2',255)->nullable();
            $table->string('thr_sub3',255)->nullable();
            $table->string('thr_sub4',255)->nullable();
            $table->string('thr_sub5',150)->nullable();
            $table->string('thr_sub6',255)->nullable();
            $table->string('thr_sub7',255)->nullable();
            $table->string('thr_sub8',255)->nullable();
            $table->string('thr_sub9',255)->nullable();
            $table->string('thr_sub10',255)->nullable();
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
        Schema::dropIfExists('thrive_visitors');
    }
}
