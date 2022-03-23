<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPrioriteMontantRealiseToEcheanciersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('echeanciers', function (Blueprint $table) {
            $table->integer('priorite')->nullable();
            $table->decimal('montant_realise', 15,3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('echeanciers', function (Blueprint $table) {
            $table->dropColumn('priorite');
            $table->dropColumn('montant_realise');
        });
    }
}
