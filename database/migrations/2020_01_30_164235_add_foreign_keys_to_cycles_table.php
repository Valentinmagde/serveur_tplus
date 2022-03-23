<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCyclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cycles', function(Blueprint $table)
		{
			$table->foreign('associations_id', 'fk_cycles_associations_id')->references('id')->on('associations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cycles', function(Blueprint $table)
		{
			$table->dropForeign('fk_cycles_associations_id');
		});
	}

}
