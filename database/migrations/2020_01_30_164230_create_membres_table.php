<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMembresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('membres', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('associations_id')->index('fk_membres_associations_id');
			$table->string('firstName', 191)->nullable();
			$table->integer('date_created')->nullable();
			$table->string('created_by', 45)->nullable();
			$table->string('code', 45)->nullable();
			$table->string('lastName', 191)->nullable();
			$table->string('etat', 45)->nullable();
			$table->integer('create_at')->nullable();
			$table->integer('update_at')->nullable();
			$table->string('adresse', 200)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('membres');
	}

}
