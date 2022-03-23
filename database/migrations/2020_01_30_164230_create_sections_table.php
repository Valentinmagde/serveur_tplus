<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sections', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('rapports_id')->index('fk_sections_rapports_id');
			$table->integer('create_at')->nullable();
			$table->integer('update_at')->nullable();
			$table->string('titre', 45)->nullable();
			$table->string('contenu', 45)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sections');
	}

}
