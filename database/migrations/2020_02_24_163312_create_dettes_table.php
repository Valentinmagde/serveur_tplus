<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDettesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dettes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('libelle', 191)->nullable();
            $table->decimal('montant', 15,3)->nullable();
            $table->integer('date')->nullable();

            $table->integer('comptes_id')->index('fk_dettes_comptes_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dettes');
    }
}
