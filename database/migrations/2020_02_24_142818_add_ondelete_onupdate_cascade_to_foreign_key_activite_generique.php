<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyActiviteGenerique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activites_generiques', function(Blueprint $table)
		{
            $table->dropForeign('fk_activites_generiques_activites_id');
			$table->foreign('activites_id', 'fk_activites_generiques_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activites_generiques', function(Blueprint $table)
		{
			$table->dropForeign('fk_activites_generiques_activites_id');
		});
    }
}
