<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAssociationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('associations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('nom', 45)->nullable();
			$table->string('description', 500)->nullable();
			$table->integer('date_creation')->nullable();
			$table->string('pays', 191)->nullable();
			$table->string('ville', 191)->nullable();
			$table->string('fuseau_horaire', 191)->nullable();
			$table->string('devise', 45)->nullable();
			$table->boolean('etat')->default(0);
			$table->string('visibilite_financiere', 191)->nullable();
			$table->boolean('public')->default(0);
			$table->boolean('moderation_contenu')->default(0);
			$table->text('presentation', 65535)->nullable();
			$table->integer('create_at')->nullable();
			$table->integer('update_at')->nullable();
			$table->string('email', 191)->nullable();
			$table->string('logo', 191)->nullable();
			$table->string('slogan', 191)->nullable();
			$table->integer('admin_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('associations');
	}

}
