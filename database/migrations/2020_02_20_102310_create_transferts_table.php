<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransfertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferts', function (Blueprint $table) {
            $table->integer('id',true);
            $table->integer('expediteur')->index('fk_transferts_expediteur');
            $table->integer('recepteur')->index('fk_transferts_recepteur');
            $table->decimal('montant', 15,3);
            $table->string('libelle', 45);
            $table->string('created_by', 191);
            $table->integer('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transferts');
    }
}
