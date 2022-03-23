<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToFacturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            //
            $table->string('libelle')->nullable();
            $table->integer('nb_comptes')->default(0);
            $table->integer('periode')->default(0);
            $table->integer('prix_unitaire')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            //
            $table->dropColumn('libelle');
            $table->dropColumn('nb_comptes');
            $table->dropColumn('periode');
            $table->dropColumn('prix_unitaire');
        });
    }
}
