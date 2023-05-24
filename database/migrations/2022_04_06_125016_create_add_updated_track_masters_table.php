<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddUpdatedTrackMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('tracker_masters', function (Blueprint $table) {
         $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->after('created_at')->nullable();
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('tracker_masters', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
}
