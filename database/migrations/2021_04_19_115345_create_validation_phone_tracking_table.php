<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValidationPhoneTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('validation_phone_tracking', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('phone_number',250)->nullable();
            $table->enum('validation_type',['InternationalTelephoneValidation', 'TelephoneLineValidation','MobileValidation','SalaciousName'])->default('InternationalTelephoneValidation');
            $table->string('validation_result',30)->nullable();
            $table->text('validation_result_details')->nullable();
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
        Schema::dropIfExists('validation_phone_tracking');
    }
}
