<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('projets', function(Blueprint $table)
		{
			$table->integer('id')->primary();
			$table->integer('activites_id')->index('fk_projets_activites_id');
			$table->integer('date_debut')->nullable();
			$table->integer('date_fin')->nullable();
			$table->string('pm', 45)->nullable();
			$table->float('budget', 10, 0)->nullable();
			$table->integer('create_at')->nullable();
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
		Schema::drop('projets');
	}

}
