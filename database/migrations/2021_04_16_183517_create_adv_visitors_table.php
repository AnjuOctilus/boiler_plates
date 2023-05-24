<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_visitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('domain_id')->nullable();
            $table->unsignedBigInteger('adv_id')->nullable();
            $table->unsignedBigInteger('device_site_id')->nullable();
            $table->unsignedBigInteger('tracker_id')->nullable();
            $table->string('tracker_unique_id',191)->nullable();
            $table->string('sub_tracker',80)->nullable();
            $table->text('existingdomain')->nullable();
            $table->text('redirect_url')->nullable();
            $table->string('remote_ip',50)->nullable();
            $table->string('browser',80)->nullable();
            $table->string('os',50)->nullable();
            $table->string('country',20)->nullable();
            $table->string('timespent',255)->nullable();
            $table->string('device_type',255)->nullable();
            $table->string('resolution',100)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer_site')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('domain_id')->references('id')->on('domain_details')
                ->onDelete('cascade');;
            $table->foreign('adv_id')->references('id')->on('adv_info')
                ->onDelete('cascade');
            $table->foreign('tracker_id')->references('id')->on('tracker_masters')
                ->onDelete('cascade');
            $table->index(['domain_id','adv_id','tracker_id','tracker_unique_id','country'],'adv_visitors_index');
            // $table->foreign('device_site_id')->references('id')->on('device_site_masters')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_visitors');
    }
}
