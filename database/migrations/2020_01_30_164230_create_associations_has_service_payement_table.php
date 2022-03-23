<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAssociationsHasServicePayementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('associations_has_service_payement', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('associations_id')->index('fk_associations_has_service_payement_associations_id');
			$table->integer('service_payement_id')->index('fk_associations_has_service_payement_service_payement_id');
			$table->string('service_compte', 45)->nullable();
			$table->integer('create_at')->nullable();
			$table->integer('update_at')->nullable();
			$table->string('service_prop1', 45)->nullable();
			$table->string('service_prop2', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('associations_has_service_payement');
	}

}
