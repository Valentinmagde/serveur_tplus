<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCreditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('credits', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('membres_id')->index('fk_credits_membres_id');
			$table->integer('mutuelles_id')->index('fk_credits_mutuelles_id');
			$table->integer('mutuelles_activites_id')->index('fk_credits_mutuelles_activites_id');
			$table->integer('date_demande')->nullable();
			$table->string('etat', 45)->nullable();
			$table->string('echeance', 45)->nullable();
			$table->decimal('montant_credit', 15, 3)->nullable();
			$table->decimal('montant_interet', 15, 3)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('credits');
	}

}
