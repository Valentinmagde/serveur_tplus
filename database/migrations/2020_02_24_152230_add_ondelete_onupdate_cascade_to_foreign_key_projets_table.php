<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyProjetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projets', function(Blueprint $table)
		{
            $table->dropForeign('fk_projets_activites_id');
			$table->foreign('activites_id', 'fk_projets_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projets', function(Blueprint $table)
		{
			$table->dropForeign('fk_projets_activites_id');
		});
    }
}
