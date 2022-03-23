<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonnesAffilieesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('personnes_affiliees', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('membres_id')->index('fk_personnes_affiliees_membres_id');
			$table->integer('PAMs_id')->index('fk_personnes_affiliees_PAMs_id');
			$table->integer('PAMs_activites_id')->index('fk_personnes_affiliees_PAMs_activites_id');
			$table->integer('date_affiliation')->nullable();
			$table->string('etat', 45)->nullable();
			$table->string('nom', 45)->nullable();
			$table->string('prenom', 45)->nullable();
			$table->integer('date_naissance')->nullable();
			$table->string('numero_ID', 45)->nullable();
			$table->string('adresse', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('personnes_affiliees');
	}

}
