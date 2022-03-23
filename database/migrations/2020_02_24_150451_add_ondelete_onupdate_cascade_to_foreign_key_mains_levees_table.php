<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyMainsLeveesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mains_levees', function(Blueprint $table)
		{
            $table->dropForeign('fk_mains_levees_activites_id');
			$table->foreign('activites_id', 'fk_mains_levees_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mains_levees', function(Blueprint $table)
		{
			$table->dropForeign('fk_mains_levees_activites_id');
		});
    }
}
