<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupApiResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_api_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('contact')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('type')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('message')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('subject')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('user_type')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->text('request')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->text('response')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followup_api_responses');
    }
}
