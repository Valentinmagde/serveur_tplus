<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToMiseDeFondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('mise_de_fonds', function(Blueprint $table)
		{
            $table->foreign('mutuelles_id', 'fk_mise_de_fonds_mutuelles_id')->references('id')->on('mutuelles')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('membres_id', 'fk_mise_de_fonds_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('mise_de_fonds', function(Blueprint $table)
		{
			$table->dropForeign('fk_mise_de_fonds_mutuelles_id');
			$table->dropForeign('fk_mise_de_fonds_membres_id');
		});
    }
}
