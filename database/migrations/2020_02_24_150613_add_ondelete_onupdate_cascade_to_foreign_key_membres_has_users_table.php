<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyMembresHasUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('membres_has_users', function(Blueprint $table)
		{
            $table->dropForeign('fk_membre_has_membres_id');
			$table->dropForeign('fk_membre_has_utilisateurs_id');
			$table->foreign('membres_id', 'fk_membre_has_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('utilisateurs_id', 'fk_membre_has_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membres_has_users', function(Blueprint $table)
		{
			$table->dropForeign('fk_membre_has_membres_id');
			$table->dropForeign('fk_membre_has_utilisateurs_id');
		});
    }
}
