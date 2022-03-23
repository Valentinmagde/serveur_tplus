<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyTontinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tontines', function(Blueprint $table)
		{
            $table->dropForeign('fk_tontine_activites_id');
			$table->foreign('activites_id', 'fk_tontine_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tontines', function(Blueprint $table)
		{
			$table->dropForeign('fk_tontine_activites_id');
		});
    }
}
