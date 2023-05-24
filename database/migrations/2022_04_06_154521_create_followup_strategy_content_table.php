<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupStrategyContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_strategy_content', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->integer('template_id')->nullable();
            $table->text('subject')->nullable();
            $table->text('content')->nullable();
            $table->string('campaign_name',191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followup_strategy_content');
    }
}
