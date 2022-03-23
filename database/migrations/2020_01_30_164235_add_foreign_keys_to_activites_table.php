<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToActivitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('activites', function(Blueprint $table)
		{
			$table->foreign('associations_id', 'fk_activite_associations_id')->references('id')->on('associations')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('activites', function(Blueprint $table)
		{
			$table->dropForeign('fk_activite_associations_id');
		});
	}

}
