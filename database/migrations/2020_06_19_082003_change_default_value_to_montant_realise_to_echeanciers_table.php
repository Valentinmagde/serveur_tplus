<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultValueToMontantRealiseToEcheanciersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('ALTER TABLE echeanciers CHANGE montant_realise montant_realise decimal(15,3) default 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('echeanciers', function(Blueprint $table)
		{
			$table->dropColumn('montant_realise');
		});
    }
}
