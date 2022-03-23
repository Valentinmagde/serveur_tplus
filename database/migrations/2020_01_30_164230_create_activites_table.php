<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activites', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('associations_id')->index('fk_activite_associations_id');
			$table->string('type', 45)->nullable();
			$table->string('nom', 45)->nullable();
			$table->string('description', 45)->nullable();
			$table->string('etat', 45)->nullable();
			$table->integer('date_created')->nullable();
			$table->string('created_by', 45)->nullable();
			$table->string('taux_penalite', 45)->nullable();
			$table->boolean('gestion_automatique_avoir')->nullable()->comment('Les avoir sont utilisés automatiquement pour combler les dettes');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activites');
	}

}
