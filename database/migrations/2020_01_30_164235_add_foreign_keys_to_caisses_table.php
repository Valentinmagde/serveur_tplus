<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCaissesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('caisses', function(Blueprint $table)
		{
			$table->foreign('activites_id1', 'fk_caisses_activites_id1')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('caisses', function(Blueprint $table)
		{
			$table->dropForeign('fk_caisses_activites_id1');
		});
	}

}
