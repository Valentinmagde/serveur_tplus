<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashInsOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_ins_outs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('wallets_id')->index('fk_cash_ins_outs_wallets_id');
            $table->string('payment_id')->nullable();
            $table->string('payer_id')->nullable();
            $table->string('payer_email')->nullable();
            $table->decimal('montant', 15, 2)->default(0);
            $table->string('devise', 8);
            $table->string('status');
            $table->string('methode_paiement');
            $table->string('in_out');
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_ins_outs');
    }
}
