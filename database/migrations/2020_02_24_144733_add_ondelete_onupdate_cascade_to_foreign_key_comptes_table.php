<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyComptesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comptes', function(Blueprint $table)
		{
            $table->dropForeign('fk_compte_membres_id');
			$table->dropForeign('fk_compteactivites_id');
			$table->foreign('membres_id', 'fk_compte_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('activites_id', 'fk_compteactivites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comptes', function(Blueprint $table)
		{
			$table->dropForeign('fk_compte_membres_id');
			$table->dropForeign('fk_compteactivites_id');
		});
    }
}
