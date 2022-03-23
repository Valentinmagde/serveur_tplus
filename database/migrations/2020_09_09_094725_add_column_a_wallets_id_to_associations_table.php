<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAWalletsIdToAssociationsTable extends Migration
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
            $table->integer('a_wallets_id')->index('fk_associations_a_wallets_id')->nullable();
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
            $table->dropColumn('a_wallets_id');
        });
    }
}
