<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAssistancesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('assistances', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('membres_id')->index('fk_assistances_membres_id');
			$table->integer('solidarites_id')->index('fk_assistances_solidarites_id');
			$table->integer('solidarites_activites_id')->index('fk_assistances_solidarites_activites_id');
			$table->string('type', 45)->nullable();
			$table->decimal('montant_alloue', 15, 3)->nullable();
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
		Schema::drop('assistances');
	}

}
