<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tracker_master_id')->nullable();
            $table->unsignedBigInteger('device_site_id')->nullable();
            $table->string('tracker_unique_id')->nullable();
            $table->string('ip_address',80)->nullable();
            $table->string('browser_type',80)->nullable();
            $table->string('country',30)->nullable();
            $table->string('device_type',255)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('resolution',30)->nullable();
            $table->integer('version')->nullable();
            $table->text('referer_site')->nullable();
            $table->string('existing_domain',255)->nullable();
            $table->text('full_reference_url')->nullable();
            $table->string('affiliate_id',255)->nullable();
            $table->string('campaign',255)->nullable();
            $table->unsignedBigInteger('split_id')->nullable();
            $table->string('source',255)->nullable();
            $table->string('sub_tracker',255)->nullable();
            $table->string('tid',255)->nullable();
            $table->string('pid',255)->nullable();
            $table->unsignedBigInteger('adv_visitor_id')->nullable();
            $table->string('adv_page_name',255)->nullable();
            $table->text('adv_redirect_domain')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('split_id')->references('id')->on('split_info')
                ->onDelete('cascade');
            $table->foreign('tracker_master_id')->references('id')->on('tracker_masters')
                ->onDelete('cascade');
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
        Schema::dropIfExists('visitors');
    }
}
