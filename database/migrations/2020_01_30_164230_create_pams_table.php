<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePamsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pams', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activites_id')->index('fk_PAMs_activites_id');
			$table->string('presentation', 45)->nullable();
			$table->integer('create_at')->nullable();
			$table->string('pays', 45)->nullable();
			$table->string('ville', 45)->nullable();
			$table->string('email', 45)->nullable();
			$table->string('telephone', 45)->nullable();
			$table->decimal('montant_prime', 15, 3)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pams');
	}

}
