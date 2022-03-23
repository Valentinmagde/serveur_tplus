<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAssociationsHasServicePayementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('associations_has_service_payement', function(Blueprint $table)
		{
			$table->foreign('associations_id', 'fk_associations_has_service_payement_associations_id')->references('id')->on('assistances')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('service_payement_id', 'fk_associations_has_service_payement_service_payement_id')->references('id')->on('service_payement')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('associations_has_service_payement', function(Blueprint $table)
		{
			$table->dropForeign('fk_associations_has_service_payement_associations_id');
			$table->dropForeign('fk_associations_has_service_payement_service_payement_id');
		});
	}

}
