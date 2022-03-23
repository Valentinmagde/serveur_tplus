<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRapportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rapports', function(Blueprint $table)
		{
			$table->foreign('AGs_id', 'fk_rapport_AGs_id')->references('id')->on('ags')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rapports', function(Blueprint $table)
		{
			$table->dropForeign('fk_rapport_AGs_id');
		});
	}

}
