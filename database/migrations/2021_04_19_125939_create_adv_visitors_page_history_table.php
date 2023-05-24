<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvVisitorsPageHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_visitors_page_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('adv_visitor_id')->nullable();
            $table->text('last_visit_page')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('adv_visitor_id')->references('id')->on('adv_visitors')
                ->onDelete('cascade');
            $table->index(['adv_visitor_id'],'adv_visitors_page_history');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adv_visitors_page_history');
    }
}
