<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToActivitesGeneriquesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('activites_generiques', function(Blueprint $table)
		{
			$table->foreign('activites_id', 'fk_activites_generiques_activites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('activites_generiques', function(Blueprint $table)
		{
			$table->dropForeign('fk_activites_generiques_activites_id');
		});
	}

}
