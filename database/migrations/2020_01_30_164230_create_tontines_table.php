<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTontinesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tontines', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activites_id')->index('fk_tontine_activites_id');
			$table->string('type', 45)->nullable();
			$table->decimal('montant_part', 15, 3)->nullable();
			$table->decimal('montant_cagnote', 15, 3)->nullable();
			$table->string('date_debut', 45)->nullable();
			$table->string('duree', 45)->nullable();
			$table->string('minimum_enchere', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tontines');
	}

}
