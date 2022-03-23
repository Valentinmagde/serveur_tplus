<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnMontantFondToSolidaritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('ALTER TABLE solidarites CHANGE montant_fond_de_caisse montant_fond_solidarite decimal(15,3) default 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('solidarites', function(Blueprint $table)
		{
			$table->dropColumn('montant_fond_solidarite');
		});
    }
}
