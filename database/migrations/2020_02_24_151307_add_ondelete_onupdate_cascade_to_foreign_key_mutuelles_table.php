<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyMutuellesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mutuelles', function(Blueprint $table)
		{
            $table->dropForeign('fk_mutuelles_activites_id');
			$table->foreign('activites_id', 'fk_mutuelles_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mutuelles', function(Blueprint $table)
		{
			$table->dropForeign('fk_mutuelles_activites_id');
		});
    }
}
