<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToPresenceEvenementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('presence_evenements', function(Blueprint $table)
		{
            $table->foreign('membres_id', 'fk_presence_evenements_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('evenements_id', 'fk_presence_evenements_evenements_id')->references('id')->on('evenements')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('presence_evenements', function(Blueprint $table)
		{
			$table->dropForeign('fk_presence_evenements_membres_id');
			$table->dropForeign('fk_presence_evenements_evenements_id');
		});
    }
}
