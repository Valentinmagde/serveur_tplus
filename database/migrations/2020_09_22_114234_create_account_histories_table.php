<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->integer('montant');
            $table->integer('created_at');
            $table->integer('updated_at');
            $table->integer('comptes_id')->index('fk_account_histories_comptes_id');
            $table->integer('utilisateurs_id')->index('fk_account_histories_utilisateurs_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_histories');
    }
}
