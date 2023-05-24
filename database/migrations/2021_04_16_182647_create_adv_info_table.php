<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('domain_id')->nullable();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('adv_name',255)->nullable();
            $table->string('adv_path',255)->nullable();
            $table->enum('status', ['1', '0'])->default('1');
            $table->timestamp('last_active_date')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('domain_id')->references('id')->on('domain_details')
                ->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('pages')
                ->onDelete('cascade');
            $table->index(['domain_id','page_id','status','last_active_date'],'adv_info_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_info');
    }
}
