<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionnaireOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionnaire_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('questionnaire_id')->nullable();
            $table->string('label')->nullable();
            $table->string('value')->nullable();
            $table->unsignedBigInteger('target')->nullable();
            $table->string('type')->nullable();
            $table->string('class')->nullable();
            $table->tinyInteger('flow')->nullable();
            $table->smallInteger('rank')->nullable();
            $table->integer('default_id')->nullable();
            $table->enum('status', ['1', '0'])->default('1');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('questionnaire_id')->references('id')->on('questionnaires')
                ->onDelete('cascade');
            $table->index(['questionnaire_id','status','created_at'],'op_indx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questionnaire_options');
    }
}
