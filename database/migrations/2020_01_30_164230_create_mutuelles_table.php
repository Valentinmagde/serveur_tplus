<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMutuellesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mutuelles', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activites_id')->index('fk_mutuelles_activites_id');
			$table->decimal('mise_minimum', 15, 3)->nullable();
			$table->decimal('maximum_empruntable', 15, 3)->nullable();
			$table->string('type_maximum_empruntable', 45)->nullable();
			$table->string('duree_pret', 45)->nullable();
			$table->string('taux_interet', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mutuelles');
	}

}
