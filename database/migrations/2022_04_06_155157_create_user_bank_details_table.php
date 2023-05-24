<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBankDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bank_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->integer('no_of_loans')->nullable();
            $table->string('borrow',255)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('have_reg_prev_addr',5)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('post_code',15)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('street',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('town',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('country',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('county',255)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('address1',255)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('address2',255)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('have_former_surname',5)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('former_surname',100)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('arrears',5)->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('banks')
                ->onDelete('cascade');
             $table->index(['user_id','bank_id'],'user_bank_details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_bank_details');
    }
}
