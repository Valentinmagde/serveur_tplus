<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyCaissesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caisses', function(Blueprint $table)
		{
            $table->dropForeign('fk_caisses_activites_id1');
			$table->foreign('activites_id1', 'fk_caisses_activites_id1')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caisses', function(Blueprint $table)
		{
			$table->dropForeign('fk_caisses_activites_id1');
		});
    }
}
