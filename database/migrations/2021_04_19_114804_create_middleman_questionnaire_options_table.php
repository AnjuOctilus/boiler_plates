<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiddlemanQuestionnaireOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('middleman_questionnaire_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('questionnaire_id')->nullable();
            $table->string('option_label')->nullable();
            $table->string('option_value')->nullable();
            $table->unsignedBigInteger('option_target')->nullable();
            $table->unsignedBigInteger('live_id')->nullable();
            $table->unsignedBigInteger('crm_id')->nullable();
            $table->enum('status', ['1', '0'])->default('1');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('questionnaire_id')->references('id')->on('middleman_questionnaires')
                ->onDelete('cascade');
            $table->index(['questionnaire_id','option_target','live_id','crm_id','status','created_at'],'mqo_indx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('middleman_questionnaire_options');
    }
}
