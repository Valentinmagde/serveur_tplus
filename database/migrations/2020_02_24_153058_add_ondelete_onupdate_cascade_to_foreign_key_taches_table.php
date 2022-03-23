<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyTachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taches', function(Blueprint $table)
		{
            $table->dropForeign('fk_taches_projets_activites_id');
			$table->dropForeign('fk_taches_projets_id');
			$table->dropForeign('fk_taches_utilisateurs_id');
			$table->foreign('projets_activites_id', 'fk_taches_projets_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('projets_id', 'fk_taches_projets_id')->references('id')->on('projets')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('utilisateurs_id', 'fk_taches_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taches', function(Blueprint $table)
		{
			$table->dropForeign('fk_taches_projets_activites_id');
			$table->dropForeign('fk_taches_projets_id');
			$table->dropForeign('fk_taches_utilisateurs_id');
		});
    }
}
