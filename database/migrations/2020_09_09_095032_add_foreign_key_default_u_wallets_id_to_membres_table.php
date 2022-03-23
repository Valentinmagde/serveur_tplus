<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyDefaultUWalletsIdToMembresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('membres', function (Blueprint $table) {
            $table->foreign('default_u_wallets_id', 'fk_membres_default_u_wallets_id')->references('id')->on('u_wallets')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('membres', function (Blueprint $table) {
            $table->dropForeign('fk_membres_default_u_wallets_id');
        });
    }
}
