<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTachesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('taches', function(Blueprint $table)
		{
			$table->foreign('projets_activites_id', 'fk_taches_projets_activites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('projets_id', 'fk_taches_projets_id')->references('id')->on('projets')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('utilisateurs_id', 'fk_taches_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('taches', function(Blueprint $table)
		{
			$table->dropForeign('fk_taches_projets_activites_id');
			$table->dropForeign('fk_taches_projets_id');
			$table->dropForeign('fk_taches_utilisateurs_id');
		});
	}

}
