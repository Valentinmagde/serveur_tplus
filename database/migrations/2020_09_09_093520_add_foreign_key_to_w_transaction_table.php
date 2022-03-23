<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToWTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('w_transactions', function (Blueprint $table) {
            $table->foreign('wallets_source_id', 'fk_w_transactions_wallets_source_id')->references('id')->on('wallets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('wallets_destination_id', 'fk_w_transactions_wallets_destination_id')->references('id')->on('wallets')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('w_transactions', function (Blueprint $table) {
            $table->dropForeign('fk_w_transactions_wallets_source_id');
            $table->dropForeign('fk_w_transactions_wallets_destination_id');
        });
    }
}
