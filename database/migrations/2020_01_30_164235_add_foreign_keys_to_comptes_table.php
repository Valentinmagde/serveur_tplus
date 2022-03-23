<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToComptesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('comptes', function(Blueprint $table)
		{
			$table->foreign('membres_id', 'fk_compte_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('activites_id', 'fk_compteactivites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('comptes', function(Blueprint $table)
		{
			$table->dropForeign('fk_compte_membres_id');
			$table->dropForeign('fk_compteactivites_id');
		});
	}

}
