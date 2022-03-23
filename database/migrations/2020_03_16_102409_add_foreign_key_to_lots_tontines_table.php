<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToLotsTontinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lots_tontines', function(Blueprint $table)
		{
            $table->foreign('tontines_id', 'fk_lots_tontines_tontines_id')->references('id')->on('tontines')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('comptes_id', 'fk_lots_tontines_comptes_id')->references('id')->on('comptes')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('echeanciers_id', 'fk_lots_tontines_echeanciers_id')->references('id')->on('echeanciers')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lots_tontines', function(Blueprint $table)
		{
			$table->dropForeign('fk_lots_tontines_tontines_id');
			$table->dropForeign('fk_lots_tontines_comptes_id');
			$table->dropForeign('fk_lots_tontines_echeanciers_id');
		});
    }
}
