<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyEcheanciersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('echeanciers', function(Blueprint $table)
		{
            $table->dropForeign('fk_echeancier_comptes_id');
			$table->dropForeign('fk_echeancier_membres_id');
			$table->foreign('comptes_id', 'fk_echeancier_comptes_id')->references('id')->on('comptes')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('membres_id', 'fk_echeancier_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('echeanciers', function(Blueprint $table)
		{
			$table->dropForeign('fk_echeancier_comptes_id');
			$table->dropForeign('fk_echeancier_membres_id');
		});
    }
}
