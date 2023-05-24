<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoCakeVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ho_cake_visitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('aff_id',150)->nullable();
            $table->string('aff_sub',150)->nullable();
            $table->string('aff_sub2',150)->nullable();
            $table->string('aff_sub3',150)->nullable();
            $table->string('aff_sub4',150)->nullable();
            $table->string('aff_sub5',150)->nullable();
            $table->string('offer_id',150)->nullable();
            $table->string('aff_unique1',150)->nullable();
            $table->string('aff_unique2',150)->nullable();
            $table->string('aff_unique3',150)->nullable();
            $table->string('subid1',150)->nullable();
            $table->string('subid2',150)->nullable();
            $table->string('subid3',150)->nullable();
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
        Schema::dropIfExists('ho_cake_visitors');
    }
}
