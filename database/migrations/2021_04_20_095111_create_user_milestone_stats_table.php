<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMilestoneStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_milestone_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('user_signature')->nullable();
            $table->tinyInteger('questions')->nullable();
            $table->tinyInteger('completed')->nullable();
            $table->tinyInteger('sale')->nullable();
            $table->string('source')->nullable();
            $table->tinyInteger('user_completed')->nullable();
            $table->dateTime('user_completed_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_milestone_stats');
    }
}
