<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNouvellesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nouvelles', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('associations_id')->index('fk_nouvelles_associations_id');
			$table->integer('membres_id')->index('fk_nouvelles_membres_id')->comment('Membre concerné par la nouvelle');
			$table->string('titre', 45)->nullable();
			$table->string('photo')->nullable();
			$table->text('description', 65535)->nullable();
			$table->string('categorie', 45)->nullable();
			$table->integer('date_nouvelle')->nullable();
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
		Schema::drop('nouvelles');
	}

}
