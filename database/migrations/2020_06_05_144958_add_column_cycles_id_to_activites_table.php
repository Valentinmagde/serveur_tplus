<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCyclesIdToActivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('activites', function (Blueprint $table) {
            $table->integer('cycles_id')->nullable()->index('fk_activites_cycles_id');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('activites', function (Blueprint $table) {
            $table->dropColumn('cycles_id');
        });
    }
}
