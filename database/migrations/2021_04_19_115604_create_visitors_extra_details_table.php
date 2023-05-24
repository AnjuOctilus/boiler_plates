<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitorsExtraDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitors_extra_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('ext_var1',255)->nullable();
            $table->string('ext_var2',255)->nullable();
            $table->string('ext_var3',255)->nullable();
            $table->string('ext_var4',255)->nullable();
            $table->string('ext_var5',255)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('cascade');
            $table->index(['visitor_id'],'adv_extra_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visitors_extra_details');
    }
}
