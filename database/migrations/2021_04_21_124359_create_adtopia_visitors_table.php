<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdtopiaVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adtopia_visitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('atp_source',150)->nullable();
            $table->string('atp_vendor',150)->nullable();
            $table->string('atp_sub1',150)->nullable();
            $table->string('atp_sub2',255)->nullable();
            $table->string('atp_sub3',255)->nullable();
            $table->string('atp_sub4',255)->nullable();
            $table->string('atp_sub5',255)->nullable();
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
        Schema::dropIfExists('adtopia_visitors');
    }
}
