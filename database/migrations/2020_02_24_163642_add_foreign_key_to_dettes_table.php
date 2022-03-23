<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToDettesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dettes', function(Blueprint $table)
		{
			$table->foreign('comptes_id', 'fk_dettes_comptes_id')->references('id')->on('comptes')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dettes', function(Blueprint $table)
		{
			$table->dropForeign('fk_dettes_comptes_id');
		});
    }
}
