<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEcheanciersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('echeanciers', function(Blueprint $table)
		{
			$table->foreign('comptes_id', 'fk_echeancier_comptes_id')->references('id')->on('comptes')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('membres_id', 'fk_echeancier_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('echeanciers', function(Blueprint $table)
		{
			$table->dropForeign('fk_echeancier_comptes_id');
			$table->dropForeign('fk_echeancier_membres_id');
		});
	}

}
