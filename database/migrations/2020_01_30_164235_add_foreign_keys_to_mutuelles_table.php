<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMutuellesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('mutuelles', function(Blueprint $table)
		{
			$table->foreign('activites_id', 'fk_mutuelles_activites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('mutuelles', function(Blueprint $table)
		{
			$table->dropForeign('fk_mutuelles_activites_id');
		});
	}

}
