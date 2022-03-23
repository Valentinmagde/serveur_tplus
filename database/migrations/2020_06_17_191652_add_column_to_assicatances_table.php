<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToAssicatancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('assistances', function(Blueprint $table)
		{
            $table->integer('echeances_id')->index('fk_assistances_echeances_id')->nullable();
            $table->integer('date_evenement');
            $table->integer('date_created');
            $table->integer('date_updated')->default(0);
			
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
        Schema::table('assistances', function (Blueprint $table) {
            $table->dropColumn('echeances_id');
            $table->dropColumn('date_evenement');
            $table->dropColumn('date_created');
            $table->dropColumn('date_updated');
        });
    }
}
