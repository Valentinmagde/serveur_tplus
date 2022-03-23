<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTypeAssistanceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('type_assistance', function(Blueprint $table)
		{
			$table->foreign('associations_id', 'fk_type_assistance_associations_id')->references('id')->on('associations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('type_assistance', function(Blueprint $table)
		{
			$table->dropForeign('fk_type_assistance_associations_id');
		});
	}

}
