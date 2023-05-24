<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffliateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affliate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('affiliate_name',255);
            $table->text('conversion_pixel')->nullable();
            $table->text('pixel_callback')->nullable();
            $table->enum('pixel_type',['HASOFFERS', 'ADWORDS']);
            $table->string('percentage_type',20)->nullable();
            $table->smallInteger('tracking_percentage');
            $table->smallInteger('mon_tracking');
            $table->smallInteger('tue_tracking');
            $table->smallInteger('wed_tracking');
            $table->smallInteger('thu_tracking');
            $table->smallInteger('fri_tracking');
            $table->smallInteger('sat_tracking');
            $table->smallInteger('sun_tracking');
            $table->smallInteger('weightage_percentage')->nullable();
            $table->smallInteger('mon_weightage');
            $table->smallInteger('tue_weightage');
            $table->smallInteger('wed_weightage');
            $table->smallInteger('thu_weightage');
            $table->smallInteger('fri_weightage');
            $table->smallInteger('sat_weightage');
            $table->smallInteger('sun_weightage');
            $table->smallInteger('time_zone')->nullable();
            $table->string('tracking_days',20)->nullable();
            $table->smallInteger('max_pixel_per_day')->nullable();
            $table->string('time_of_day',255)->nullable();
            $table->bigInteger('tracking_counter');
            $table->bigInteger('tracking_batch');
            $table->enum('active',['0','1']);
            $table->string('site_flag_id',255);
            $table->string('ho_offer_id',155);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affliate');
    }
}
