<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAvalistesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('avalistes', function(Blueprint $table)
		{
			$table->foreign('membres_id1', 'fk_avalistes_membres_id1')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('membres_id2', 'fk_avalistes_membres_id2')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('avalistes', function(Blueprint $table)
		{
			$table->dropForeign('fk_avalistes_membres_id1');
			$table->dropForeign('fk_avalistes_membres_id2');
		});
	}

}
