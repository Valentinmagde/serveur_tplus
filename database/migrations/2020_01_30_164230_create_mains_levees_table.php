<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMainsLeveesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mains_levees', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activites_id')->index('fk_mains_levees_activites_id');
			$table->decimal('montant_minimum', 15, 3)->nullable();
			$table->integer('date_limite')->nullable();
			$table->boolean('obligatoire')->nullable();
			$table->integer('membres_id')->comment('Bénéficiaire de la main levée');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mains_levees');
	}

}
