<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToMutuelleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('mutuelles', function (Blueprint $table) {
            $table->string('methode_calcul_interet', 191)->nullable();
            $table->string('paiement_interet', 191)->nullable();
            $table->string('remboursement', 191)->nullable();
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
        Schema::table('mutuelles', function (Blueprint $table) {
            $table->dropColumn('methode_calcul_interet');
            $table->dropColumn('paiement_interet');
            $table->dropColumn('remboursement');
        });
    }
}
