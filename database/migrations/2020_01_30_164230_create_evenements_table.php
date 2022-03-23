<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEvenementsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('evenements', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activites_id')->index('fk_evenements_activites_id');
			$table->integer('date_event')->nullable();
			$table->string('lieu_event', 45)->nullable();
			$table->string('quoi', 45)->nullable();
			$table->string('presence', 45)->nullable();
			$table->integer('date_fin')->nullable();
			$table->string('commentaire', 45)->nullable();
			$table->string('serie', 45)->nullable();
			$table->string('cycle', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('evenements');
	}

}
