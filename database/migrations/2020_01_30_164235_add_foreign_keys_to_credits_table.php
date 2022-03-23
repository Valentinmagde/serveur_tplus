<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCreditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('credits', function(Blueprint $table)
		{
			$table->foreign('membres_id', 'fk_credits_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('mutuelles_activites_id', 'fk_credits_mutuelles_activites_id')->references('id')->on('activites')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('mutuelles_id', 'fk_credits_mutuelles_id')->references('id')->on('mutuelles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('credits', function(Blueprint $table)
		{
			$table->dropForeign('fk_credits_membres_id');
			$table->dropForeign('fk_credits_mutuelles_activites_id');
			$table->dropForeign('fk_credits_mutuelles_id');
		});
	}

}
