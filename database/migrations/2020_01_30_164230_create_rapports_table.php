<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRapportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rapports', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('AGs_id')->index('fk_rapport_AGs_id');
			$table->string('resume', 45)->nullable();
			$table->string('etat', 45)->nullable();
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
		Schema::drop('rapports');
	}

}
