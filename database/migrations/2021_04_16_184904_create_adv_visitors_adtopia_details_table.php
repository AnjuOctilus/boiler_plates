<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvVisitorsAdtopiaDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_visitors_adtopia_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adv_visitor_id')->nullable();
            $table->string('atp_source',150)->nullable();
            $table->string('atp_vendor',150)->nullable();
            $table->string('atp_sub1',150)->nullable();
            $table->string('atp_sub2',150)->nullable();
            $table->string('atp_sub3',150)->nullable();
            $table->string('pid',150)->nullable();
            $table->string('acid',255)->nullable();
            $table->string('cid',255)->nullable();
            $table->string('crvid',255)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('adv_visitor_id')->references('id')->on('adv_visitors')
                ->onDelete('cascade');
            $table->index(['adv_visitor_id'],'adv_adtopia_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_visitors_adtopia_details');
    }
}
