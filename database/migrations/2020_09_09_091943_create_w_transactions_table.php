<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_transactions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->decimal('montant', 15, 2)->default(0);
            $table->decimal('taux_change', 15, 2)->default(0);
            $table->decimal('frais', 15, 2)->default(0);
            $table->integer('date_transaction');
            $table->string('devise_source');
            $table->string('devise_destination');
            $table->string('type');
            $table->string('status');
            $table->string('details', 500);
            $table->integer('wallets_source_id')->index('fk_w_transactions_wallets_source_id');
            $table->integer('wallets_destination_id')->index('fk_w_transactions_wallets_destination_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('w_transactions');
    }
}
