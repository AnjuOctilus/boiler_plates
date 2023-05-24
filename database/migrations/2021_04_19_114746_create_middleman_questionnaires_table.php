<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiddlemanQuestionnairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('middleman_questionnaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('question_title')->nullable();
            $table->string('form_type')->nullable();
            $table->integer('parent_id')->nullable();
            $table->unsignedBigInteger('live_id')->nullable();
            $table->unsignedBigInteger('crm_id')->nullable();
            $table->enum('status', ['1', '0'])->default('1');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['live_id','crm_id','status','created_at'],'mq_indx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('middleman_questionnaires');
    }
}
