<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAvalistesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('avalistes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('membres_id1')->index('fk_avalistes_membres_id1');
			$table->integer('membres_id2')->index('fk_avalistes_membres_id2');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('avalistes');
	}

}
