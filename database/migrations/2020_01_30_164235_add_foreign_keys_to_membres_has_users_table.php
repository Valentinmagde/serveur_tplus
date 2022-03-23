<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMembresHasUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('membres_has_users', function(Blueprint $table)
		{
			$table->foreign('membres_id', 'fk_membre_has_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('utilisateurs_id', 'fk_membre_has_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('membres_has_users', function(Blueprint $table)
		{
			$table->dropForeign('fk_membre_has_membres_id');
			$table->dropForeign('fk_membre_has_utilisateurs_id');
		});
	}

}
