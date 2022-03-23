<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('comptes_id')->index('fk_transaction_comptes_id');
			$table->integer('operations_id')->index('fk_transaction_operations_id');
			$table->decimal('montant', 15, 3)->nullable();
			$table->string('libelle', 45)->nullable();
			$table->string('etat', 45)->nullable();
			$table->integer('date_created')->nullable();
			$table->string('created_by', 45)->nullable();
			$table->string('debit_credit', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transactions');
	}

}
