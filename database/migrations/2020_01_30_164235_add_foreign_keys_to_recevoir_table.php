<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRecevoirTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('recevoir', function(Blueprint $table)
		{
			$table->foreign('utilisateurs_id', 'fk_recevoir_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('messages_id', 'fk_recevoirmessages_id')->references('id')->on('messages')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('recevoir', function(Blueprint $table)
		{
			$table->dropForeign('fk_recevoir_utilisateurs_id');
			$table->dropForeign('fk_recevoirmessages_id');
		});
	}

}
