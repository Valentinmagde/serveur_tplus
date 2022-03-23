<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToUWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('u_wallets', function (Blueprint $table) {
            $table->foreign('utilisateurs_id', 'fk_u_wallets_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('wallets_id', 'fk_u_wallets_wallets_id')->references('id')->on('wallets')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('u_wallets', function (Blueprint $table) {
            $table->dropForeign('fk_u_wallets_utilisateurs_id');
            $table->dropForeign('fk_u_wallets_wallets_id');
        });
    }
}
