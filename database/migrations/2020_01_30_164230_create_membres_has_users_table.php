<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMembresHasUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('membres_has_users', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('utilisateurs_id')->index('fk_membre_has_utilisateurs_id');
			$table->integer('membres_id')->index('fk_membre_has_membres_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('membres_has_users');
	}

}
