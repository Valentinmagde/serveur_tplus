<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPersonnesAffilieesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('personnes_affiliees', function(Blueprint $table)
		{
			$table->foreign('PAMs_activites_id', 'fk_personnes_affiliees_PAMs_activites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('PAMs_id', 'fk_personnes_affiliees_PAMs_id')->references('id')->on('pams')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('membres_id', 'fk_personnes_affiliees_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('personnes_affiliees', function(Blueprint $table)
		{
			$table->dropForeign('fk_personnes_affiliees_PAMs_activites_id');
			$table->dropForeign('fk_personnes_affiliees_PAMs_id');
			$table->dropForeign('fk_personnes_affiliees_membres_id');
		});
	}

}
