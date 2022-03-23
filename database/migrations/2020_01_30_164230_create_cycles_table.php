<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCyclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cycles', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('associations_id')->index('fk_cycles_associations_id');
			$table->integer('duree_cycle');
			$table->string('mesure_cycle', 45)->nullable();
			$table->string('type_assemblee')->nullable();
			$table->integer('date_premiere_assemblee')->nullable();
			$table->string('heure_assemblee', 10)->nullable();
			$table->float('participation_reception', 10, 0)->nullable();
			$table->float('sanction_retard', 10, 0)->nullable();
			$table->float('sanction_abscence', 10, 0)->nullable();
			$table->float('frais_inscription', 10, 0)->nullable();
			$table->integer('date_lim_frais_insc')->nullable();
			$table->string('frequence_seance', 45)->nullable();
			$table->integer('jour_semaine');
			$table->integer('jour_mois')->nullable();
			$table->integer('ordre_semaine')->nullable();
			$table->dateTime('create_at')->nullable();
			$table->integer('create_by')->nullable();
			$table->dateTime('update_at')->nullable();
			$table->integer('update_by')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cycles');
	}

}
