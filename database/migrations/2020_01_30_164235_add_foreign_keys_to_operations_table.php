<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOperationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('operations', function(Blueprint $table)
		{
			$table->foreign('membre_id', 'fk_operations_membre_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('operations', function(Blueprint $table)
		{
			$table->dropForeign('fk_operations_membre_id');
		});
	}

}
