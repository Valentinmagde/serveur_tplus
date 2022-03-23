<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEcheanciersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('echeanciers', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('membres_id')->index('fk_echeancier_membres_id');
			$table->integer('comptes_id')->index('fk_echeancier_comptes_id');
			$table->decimal('montant', 15, 3)->nullable();
			$table->string('debit_credit', 45)->nullable();
			$table->string('libelle', 45)->nullable();
			$table->string('date_limite', 45)->nullable();
			$table->string('etat', 45)->nullable();
			$table->string('created_by', 45)->nullable();
			$table->integer('date_created')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('echeanciers');
	}

}
