<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLotsTontinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lots_tontines', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('montant', 15,3)->nullable();
            $table->integer('date_bouffe')->nullable();
            $table->string('created_by')->nullable();
            $table->integer('date_created')->nullable();
            $table->string('updated_by')->nullable();
            $table->integer('date_updated')->nullable();
            $table->integer('tontines_id')->index('fk_lots_tontines_tontines_id');
            $table->integer('comptes_id')->nullable()->index('fk_lots_tontines_comptes_id');
            $table->integer('echeanciers_id')->nullable()->index('fk_lots_tontines_echeanciers_id');
            $table->string('etat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lots_tontines');
    }
}
