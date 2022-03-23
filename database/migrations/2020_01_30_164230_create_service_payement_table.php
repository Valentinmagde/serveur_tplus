<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServicePayementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('service_payement', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('nom', 45)->nullable();
			$table->string('type', 45)->nullable();
			$table->string('pays_disponible', 45)->nullable();
			$table->string('logo', 45)->nullable();
			$table->string('etat', 45)->nullable();
			$table->string('service_key', 45)->nullable();
			$table->string('service_pass', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('service_payement');
	}

}
