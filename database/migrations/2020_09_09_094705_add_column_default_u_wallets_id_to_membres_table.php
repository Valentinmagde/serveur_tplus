<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDefaultUWalletsIdToMembresTable extends Migration
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
            $table->integer('default_u_wallets_id')->nullable()->index('fk_membres_default_u_wallets_id');
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
            $table->dropColumn('default_u_wallets_id');
        });
    }
}
