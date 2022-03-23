<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCommentaireNouvellesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('commentaire_nouvelles', function(Blueprint $table)
		{
			$table->foreign('membres_id', 'fk_membre_membres_id')->references('id')->on('membres')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('nouvelles_id', 'fk_nouvelle_nouvelles_id')->references('id')->on('nouvelles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('commentaire_nouvelles', function(Blueprint $table)
		{
			$table->dropForeign('fk_membre_membres_id');
			$table->dropForeign('fk_nouvelle_nouvelles_id');
		});
	}

}
