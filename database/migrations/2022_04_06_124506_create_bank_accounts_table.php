<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bank_id')->nullable()->default(NULL);
            $table->string('account_name')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->string('account_code')->collate('utf8mb4_unicode_ci')->nullable()->default(NULL);
            $table->enum('status', ['1','0'])->default('1');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
              $table->foreign('bank_id')->references('id')->on('banks')
                ->onDelete('cascade');
            $table->index(['bank_id','status','created_at'],'bank_account_index');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_accounts');
    }
}
