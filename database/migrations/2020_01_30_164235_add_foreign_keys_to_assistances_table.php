<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAssistancesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('assistances', function(Blueprint $table)
		{
			$table->foreign('membres_id', 'fk_assistances_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('solidarites_activites_id', 'fk_assistances_solidarites_activites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('solidarites_id', 'fk_assistances_solidarites_id')->references('id')->on('solidarites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('assistances', function(Blueprint $table)
		{
			$table->dropForeign('fk_assistances_membres_id');
			$table->dropForeign('fk_assistances_solidarites_activites_id');
			$table->dropForeign('fk_assistances_solidarites_id');
		});
	}

}
