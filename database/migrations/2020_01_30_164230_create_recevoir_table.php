<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRecevoirTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recevoir', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('utilisateurs_id')->index('fk_recevoir_utilisateurs_id');
			$table->integer('messages_id')->index('fk_recevoirmessages_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('recevoir');
	}

}
