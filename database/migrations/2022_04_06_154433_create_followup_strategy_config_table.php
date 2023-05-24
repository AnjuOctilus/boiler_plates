<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupStrategyConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_strategy_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('strategy_name',191)->nullable();
            $table->integer('percentage')->nullable();
            $table->string('strategy_type',191)->nullable();
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
        Schema::dropIfExists('followup_strategy_config');
    }
}
