<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostcodeValidationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postcode_validation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('post_code',255)->nullable();
            $table->string('address',255)->nullable();
            $table->string('credits_display_text',255)->nullable();
            $table->string('lookup_id',255)->nullable();
            $table->string('organisation',255)->nullable();
            $table->string('line1',255)->nullable();
            $table->string('line2',255)->nullable();
            $table->string('line3',255)->nullable();
            $table->string('town',255)->nullable();
            $table->string('county',255)->nullable();
            $table->string('country',255)->nullable();
            $table->string('deliverypointsuffix',255)->nullable();
            $table->string('nohouseholds',255)->nullable();
            $table->string('smallorg',255)->nullable();
            $table->string('pobox',255)->nullable();
            $table->string('rawpostcode',255)->nullable();
            $table->string('pz_mailsort',255)->nullable();
            $table->string('spare',255)->nullable();
            $table->string('udprn',255)->nullable();
            $table->string('fl_unique',255)->nullable();
            $table->string('status')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postcode_validation');
    }
}
