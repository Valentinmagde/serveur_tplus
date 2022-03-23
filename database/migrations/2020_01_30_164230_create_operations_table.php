<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOperationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('operations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('date_realisation')->nullable();
			$table->string('debit_credit', 45)->nullable();
			$table->string('enregistre_par', 45)->nullable();
			$table->decimal('montant', 15, 3)->nullable();
			$table->string('etat', 45)->nullable();
			$table->string('mode', 45)->nullable();
			$table->string('preuve', 45)->nullable();
			$table->boolean('en_ligne')->nullable();
			$table->integer('membre_id')->index('fk_operations_membre_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('operations');
	}

}
