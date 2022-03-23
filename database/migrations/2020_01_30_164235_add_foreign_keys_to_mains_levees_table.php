<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMainsLeveesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('mains_levees', function(Blueprint $table)
		{
			$table->foreign('activites_id', 'fk_mains_levees_activites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('mains_levees', function(Blueprint $table)
		{
			$table->dropForeign('fk_mains_levees_activites_id');
		});
	}

}
