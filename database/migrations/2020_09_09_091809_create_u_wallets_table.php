<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('u_wallets', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('wallets_id')->index('fk_u_wallets_wallets_id');
            $table->integer('utilisateurs_id')->index('fk_u_wallets_utilisateurs_id');
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
        Schema::dropIfExists('u_wallets');
    }
}
