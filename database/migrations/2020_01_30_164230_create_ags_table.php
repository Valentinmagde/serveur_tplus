<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAgsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ags', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('cycles_id')->index('fk_AGs_cycles_id');
			$table->integer('membres_id')->nullable()->index('fk_AGs_membres_id')->comment('Membre qui reçoit la réunion ou qui l’organise');
			$table->integer('create_at')->nullable();
			$table->integer('update_at')->nullable();
			$table->integer('date_ag')->nullable();
			$table->string('lieu_ag', 200)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ags');
	}

}
