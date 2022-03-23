<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFacturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('factures', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('cycles_id')->index('fk_facture_cycles_id');
			$table->string('statut', 45)->nullable();
			$table->string('mode_paiement', 45)->nullable();
			$table->string('code_promo', 45)->nullable();
			$table->float('reduction', 10, 0)->nullable();
			$table->integer('date_paye')->nullable();
			$table->integer('delais_paiement')->nullable();
			$table->decimal('montant', 15, 3)->nullable();
			$table->integer('create_at')->nullable();
			$table->integer('update_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('factures');
	}

}
