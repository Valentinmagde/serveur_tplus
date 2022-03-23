<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyPersonnesAffilieesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personnes_affiliees', function(Blueprint $table)
		{
            $table->dropForeign('fk_personnes_affiliees_PAMs_activites_id');
			$table->dropForeign('fk_personnes_affiliees_PAMs_id');
			$table->dropForeign('fk_personnes_affiliees_membres_id');
			$table->foreign('PAMs_activites_id', 'fk_personnes_affiliees_PAMs_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('PAMs_id', 'fk_personnes_affiliees_PAMs_id')->references('id')->on('pams')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('membres_id', 'fk_personnes_affiliees_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personnes_affiliees', function(Blueprint $table)
		{
			$table->dropForeign('fk_personnes_affiliees_PAMs_activites_id');
			$table->dropForeign('fk_personnes_affiliees_PAMs_id');
			$table->dropForeign('fk_personnes_affiliees_membres_id');
		});
    }
}
