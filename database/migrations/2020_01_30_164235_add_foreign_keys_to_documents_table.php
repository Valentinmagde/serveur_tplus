<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDocumentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('documents', function(Blueprint $table)
		{
			$table->foreign('associations_id', 'fk_documents_associations1')->references('id')->on('associations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('associations_id', 'fk_documents_associations_id')->references('id')->on('associations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('documents', function(Blueprint $table)
		{
			$table->dropForeign('fk_documents_associations1');
			$table->dropForeign('fk_documents_associations_id');
		});
	}

}
