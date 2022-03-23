<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNouvellesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nouvelles', function(Blueprint $table)
		{
			$table->foreign('associations_id', 'fk_nouvelles_associations_id')->references('id')->on('associations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('membres_id', 'fk_nouvelles_membres_id')->references('id')->on('membres')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('nouvelles', function(Blueprint $table)
		{
			$table->dropForeign('fk_nouvelles_associations_id');
			$table->dropForeign('fk_nouvelles_membres_id');
		});
	}

}
