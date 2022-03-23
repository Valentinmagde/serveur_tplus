<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToAWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('a_wallets', function (Blueprint $table) {
            $table->foreign('wallets_id', 'fk_a_wallets_wallets_id')->references('id')->on('wallets')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('a_wallets', function (Blueprint $table) {
            $table->dropForeign('fk_a_wallets_wallets_id');
        });
    }
}
