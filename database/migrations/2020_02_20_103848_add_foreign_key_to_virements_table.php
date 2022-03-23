<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToVirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('virements', function(Blueprint $table)
		{
			$table->foreign('comptes_id', 'fk_virements_comptes_id')->references('id')->on('comptes');
			$table->foreign('activites_id', 'fk_virements_activites_id')->references('id')->on('activites');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //

        Schema::table('virements', function(Blueprint $table)
		{
			$table->dropForeign('fk_virements_comptes_id');
			$table->dropForeign('fk_virements_activites_id');
		});
    }
}
