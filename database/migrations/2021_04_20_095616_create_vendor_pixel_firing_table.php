<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorPixelFiringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_pixel_firing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('vendor',255)->nullable();
            $table->enum('page_type', ['LP', 'TY', 'CN','AP'])->default('LP');
            $table->enum('pixel_type', ['web', 'API'])->default('web');
            $table->text('pixel_log')->nullable();
            $table->text('pixel_result')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_pixel_firing');
    }
}
