<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToCashInsOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('cash_ins_outs', function (Blueprint $table) {
            $table->foreign('wallets_id', 'fk_cash_ins_outs_wallets_id')->references('id')->on('wallets')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('cash_ins_outs', function (Blueprint $table) {
            $table->dropForeign('fk_cash_ins_outs_wallets_id');
        });
    }
}
