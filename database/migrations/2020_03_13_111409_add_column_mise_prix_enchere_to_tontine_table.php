<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMisePrixEnchereToTontineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tontines', function (Blueprint $table) {
            $table->decimal('maximum_enchere',15,3)->default(0);
            $table->decimal('mise_prix_enchere',15,3)->default(0);
            $table->integer('taux_emprunt_petits_lots')->default(0);
            $table->integer('delais_remboursement_petits_lots')->nullable();
            $table->integer('date_fin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tontines', function (Blueprint $table) {
            $table->dropColumn('maximum_enchere');
            $table->dropColumn('mise_prix_enchere');
            $table->dropColumn('taux_emprunt_petits_lots');
            $table->dropColumn('delais_remboursement_petits_lots');
            $table->dropColumn('date_fin');
        });
    }
}
