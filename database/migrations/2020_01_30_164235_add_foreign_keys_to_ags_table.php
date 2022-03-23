<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAgsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ags', function(Blueprint $table)
		{
			$table->foreign('cycles_id', 'fk_AGs_cycles_id')->references('id')->on('cycles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('membres_id', 'fk_AGs_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ags', function(Blueprint $table)
		{
			$table->dropForeign('fk_AGs_cycles_id');
			$table->dropForeign('fk_AGs_membres_id');
		});
	}

}
