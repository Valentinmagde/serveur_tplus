<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyAWalletsIdToAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('associations', function (Blueprint $table) {
            $table->foreign('a_wallets_id', 'fk_associations_a_wallets_id')->references('id')->on('a_wallets')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('associations', function (Blueprint $table) {
            $table->dropForeign('fk_associations_a_wallets_id');
        });
    }
}
