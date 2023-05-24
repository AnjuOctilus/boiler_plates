<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvClickDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_click_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adv_visitor_id')->nullable();
            $table->unsignedBigInteger('adv_id')->nullable();
            $table->string('remote_ip')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->string('time_spent',50)->nullable();
            $table->string('resolution',50)->nullable();
            $table->string('click_link',250)->nullable();
            $table->text('link_url')->nullable();
            $table->string('page',50)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('adv_visitor_id')->references('id')->on('adv_visitors')
                ->onDelete('cascade');
            $table->foreign('adv_id')->references('id')->on('adv_info')
                ->onDelete('cascade');
            $table->index(['adv_id','adv_visitor_id'],'adv_click_details_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_click_details');
    }
}
