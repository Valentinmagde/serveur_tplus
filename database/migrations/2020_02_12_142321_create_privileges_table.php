<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('privileges', function (Blueprint $table) {
            $table->integer('roles_id')->index('fk_privileges_roles_id');
            $table->integer('utilisateurs_id')->index('fk_privileges_utilisateurs_id');
            $table->integer('membres_has_users_id')->index('fk_privileges_membres_has_users_id');
            $table->integer('associations_id')->index('fk_privileges_associations_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('privileges');
    }
}
