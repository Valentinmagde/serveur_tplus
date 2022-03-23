<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentaireNouvellesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('commentaire_nouvelles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('nouvelles_id')->index('fk_nouvelle_nouvelles_id');
			$table->integer('membres_id')->index('fk_membre_membres_id');
			$table->boolean('aime')->nullable();
			$table->boolean('aime_pas')->nullable();
			$table->text('commentaire', 65535)->nullable();
			$table->integer('created_at')->nullable();
			$table->integer('updated_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('commentaire_nouvelles');
	}

}
