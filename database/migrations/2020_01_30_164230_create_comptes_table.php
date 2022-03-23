<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateComptesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comptes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activites_id')->index('fk_compteactivites_id');
			$table->integer('membres_id')->index('fk_compte_membres_id');
			$table->decimal('dette', 15, 3)->nullable();
			$table->decimal('avoir_anterieur', 15, 3)->nullable();
			$table->string('nombre_noms', 45)->nullable();
			$table->decimal('montant_cotisation', 15, 3)->nullable();
			$table->string('avoir', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('comptes');
	}

}
