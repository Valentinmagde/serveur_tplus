<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('transactions', function(Blueprint $table)
		{
			$table->foreign('comptes_id', 'fk_transaction_comptes_id')->references('id')->on('comptes')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('operations_id', 'fk_transaction_operations_id')->references('id')->on('operations')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transactions', function(Blueprint $table)
		{
			$table->dropForeign('fk_transaction_comptes_id');
			$table->dropForeign('fk_transaction_operations_id');
		});
	}

}
