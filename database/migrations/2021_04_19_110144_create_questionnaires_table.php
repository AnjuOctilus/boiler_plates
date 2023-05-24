<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionnairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->text('title')->nullable();
            $table->enum('is_required', ['yes', 'no'])->default('yes');
            $table->enum('type',['questionnaire0','questionnaire1', 'questionnaire2'])->default('questionnaire1');
            $table->string('form_type')->nullable();
            $table->integer('default_id')->nullable();
            $table->integer('parent')->nullable();
            $table->string('extra_param')->nullable();
            $table->enum('status', ['1', '0'])->default('1'); 
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['bank_id','is_required','type','status','created_at'],'q_indx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questionnaires');
    }
}
