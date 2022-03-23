<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToPrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('privileges', function(Blueprint $table)
		{
            $table->foreign('roles_id', 'fk_privileges_roles_id')->references('id')->on('roles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('membres_has_users_id', 'fk_privileges_membres_has_users_id')->references('id')->on('membres_has_users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('utilisateurs_id', 'fk_privileges_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('associations_id', 'fk_privileges_associations_id')->references('id')->on('associations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('privilleges', function(Blueprint $table)
		{
            $table->dropForeign('fk_privileges_roles_id');
            $table->dropForeign('fk_privileges_membres_has_users_id');
            $table->dropForeign('fk_privileges_utilisateurs_id');
            $table->dropForeign('fk_privileges_associations_id');
		});
    }
}
