<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToFacturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('factures', function(Blueprint $table)
		{
			$table->foreign('cycles_id', 'fk_facture_cycles_id')->references('id')->on('cycles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('factures', function(Blueprint $table)
		{
			$table->dropForeign('fk_facture_cycles_id');
		});
	}

}
