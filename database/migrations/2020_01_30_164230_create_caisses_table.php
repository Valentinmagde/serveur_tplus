<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCaissesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('caisses', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activites_id1')->index('fk_caisses_activites_id1');
			$table->string('en_caisse', 45)->nullable();
			$table->string('etat', 45)->nullable();
			$table->integer('date_created')->nullable();
			$table->string('created_by', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('caisses');
	}

}
