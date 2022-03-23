<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTypeAssistanceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('type_assistance', function(Blueprint $table)
		{
			$table->integer('id')->primary();
			$table->integer('associations_id')->index('fk_type_assistance_associations_id');
			$table->string('Nom', 45)->nullable();
			$table->string('montant', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('type_assistance');
	}

}
