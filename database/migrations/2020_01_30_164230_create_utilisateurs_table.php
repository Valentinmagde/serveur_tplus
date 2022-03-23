<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUtilisateursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('utilisateurs', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('firstName', 191)->nullable();
			$table->string('lastName', 191)->nullable();
			$table->string('email', 191)->nullable();
			$table->string('phone', 191)->nullable();
			$table->string('password', 191)->nullable();
			$table->string('source', 25)->nullable();
			$table->string('sexe', 191)->nullable();
			$table->integer('date_nais')->nullable();
			$table->string('photo_couverture', 191)->nullable();
			$table->string('photo_profil', 191)->nullable();
			$table->string('remember_token', 191)->nullable();
			$table->integer('active')->default(0);
			$table->string('pays', 191)->nullable();
			$table->string('ville', 191)->nullable();
			$table->string('anniversaire', 191)->nullable();
			$table->integer('created_at')->nullable();
			$table->integer('updated_at')->nullable();
			$table->string('activation_token', 191);
			$table->string('code', 191)->nullable();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('utilisateurs');
	}

}
