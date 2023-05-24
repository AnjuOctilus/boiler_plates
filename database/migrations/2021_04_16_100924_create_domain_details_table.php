<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('domain_name')->nullable();
            $table->enum('type', ['Both','LP','Adv'])->default('Both');
            $table->enum('status', ['1', '0'])->default('1');
            $table->timestamp('last_active_date')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['type','last_active_date','status'],'domain_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_details');
    }
}
