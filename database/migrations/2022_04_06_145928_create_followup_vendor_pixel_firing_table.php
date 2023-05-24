<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupVendorPixelFiringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_vendor_pixel_firing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('followup_visit_id')->nullable();
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('vendor')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->enum('page_type', ['LP', 'TY', 'CN'])->collate('utf8mb4_unicode_ci')->default('LP');
            $table->enum('pixel_type', ['web', 'API'])->collate('utf8mb4_unicode_ci')->default('web');
            $table->text('pixel_log')->collate('utf8mb4_unicode_ci')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['followup_visit_id','visitor_id','user_id','page_type','pixel_type','created_at'],'followup_vendor_pixel_firing_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followup_vendor_pixel_firing');
    }
}
