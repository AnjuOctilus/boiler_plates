<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvVisitorsTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_visitors_temp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adv_visitor_id')->nullable();
            $table->unsignedBigInteger('adv_id')->nullable();  
            $table->unsignedBigInteger('tracker_id')->nullable();
            $table->unsignedBigInteger('device_site_id')->nullable();
            $table->unsignedBigInteger('tracker_unique_id')->nullable();
            $table->string('remote_ip')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('adv_visitor_id')->references('id')->on('adv_visitors')
            ->onDelete('cascade');
            $table->foreign('tracker_id')->references('id')->on('tracker_masters')
            ->onDelete('cascade');
            // $table->foreign('device_site_id')->references('id')->on('device_site_masters')
            //     ->onDelete('cascade');
            $table->index(['adv_id','tracker_id','tracker_unique_id','device_site_id','country'],'adv_visitors_temp_index');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_visitors_temp');
    }
}
