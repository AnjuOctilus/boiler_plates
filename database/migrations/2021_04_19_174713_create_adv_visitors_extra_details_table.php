<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvVisitorsExtraDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_visitors_extra_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adv_visitor_id')->nullable();
            $table->string('ext_var1',225)->nullable();
            $table->string('ext_var2',225)->nullable();
            $table->string('ext_var3',225)->nullable();
            $table->string('ext_var4',225)->nullable();
            $table->string('ext_var5',225)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('adv_visitor_id')->references('id')->on('adv_visitors')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_visitors_extra_details');
    }
}
