<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_visit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('atp_sub2',255)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('tracker_unique_id',20)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->text('request')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->integer('fireflag')->nullable();
            $table->longText('adtopia_response')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('type')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('source')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
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
        Schema::dropIfExists('followup_visit');
    }
}
