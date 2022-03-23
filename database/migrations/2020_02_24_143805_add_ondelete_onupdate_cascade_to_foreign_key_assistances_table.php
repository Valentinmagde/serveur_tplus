<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyAssistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assistances', function(Blueprint $table)
		{
            $table->dropForeign('fk_assistances_membres_id');
			$table->dropForeign('fk_assistances_solidarites_activites_id');
			$table->dropForeign('fk_assistances_solidarites_id');
			$table->foreign('membres_id', 'fk_assistances_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('solidarites_activites_id', 'fk_assistances_solidarites_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('solidarites_id', 'fk_assistances_solidarites_id')->references('id')->on('solidarites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assistances', function(Blueprint $table)
		{
			$table->dropForeign('fk_assistances_membres_id');
			$table->dropForeign('fk_assistances_solidarites_activites_id');
			$table->dropForeign('fk_assistances_solidarites_id');
		});
    }
}
