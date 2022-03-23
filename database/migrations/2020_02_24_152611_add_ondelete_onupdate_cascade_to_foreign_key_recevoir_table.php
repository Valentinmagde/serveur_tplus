<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyRecevoirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recevoir', function(Blueprint $table)
		{
            $table->dropForeign('fk_recevoir_utilisateurs_id');
			$table->dropForeign('fk_recevoirmessages_id');
			$table->foreign('utilisateurs_id', 'fk_recevoir_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('messages_id', 'fk_recevoirmessages_id')->references('id')->on('messages')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recevoir', function(Blueprint $table)
		{
			$table->dropForeign('fk_recevoir_utilisateurs_id');
			$table->dropForeign('fk_recevoirmessages_id');
		});
    }
}
