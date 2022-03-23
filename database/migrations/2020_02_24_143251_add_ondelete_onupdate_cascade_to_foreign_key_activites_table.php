<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyActivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activites', function(Blueprint $table)
		{
            $table->dropForeign('fk_activite_associations_id');
			$table->foreign('associations_id', 'fk_activite_associations_id')->references('id')->on('associations')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activites', function(Blueprint $table)
		{
			$table->dropForeign('fk_activite_associations_id');
		});
    }
}
