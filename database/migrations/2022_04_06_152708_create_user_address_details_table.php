<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAddressDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_address_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->default(NULL);
            $table->tinyInteger('address_type')->comment('0 - Primary, 1,2,3 - Previous address')->default('0');
            $table->string('postcode')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('address_line1')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('address_line2')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('address_line3')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('address_line4')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('town')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('locality')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('county')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('district')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('country')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('vendor')->collate('utf8mb4_unicode_ci')->nullable()->default('getaddress');
            $table->string('address_id')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->tinyInteger('is_manual')->default('0');
            $table->tinyInteger('approve_status')->default('0');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['user_id','address_type','created_at'],'user_address_details_index');

        });
    } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_address_details');
    }
}
