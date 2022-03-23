<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTachesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taches', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('projets_id')->index('fk_taches_projets_id');
			$table->integer('projets_activites_id')->index('fk_taches_projets_activites_id');
			$table->integer('utilisateurs_id')->index('fk_taches_utilisateurs_id');
			$table->string('nom', 45)->nullable();
			$table->integer('date_debut')->nullable();
			$table->integer('date_fin')->nullable();
			$table->decimal('budget', 15, 3)->nullable();
			$table->string('etat', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('taches');
	}

}
