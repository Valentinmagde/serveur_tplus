<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMontantRestantAndDateLimiteToCreditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('credits', function (Blueprint $table) {
            $table->decimal('montant_restant', 15,3)->default(0);
            $table->integer('date_limite')->default(0);  
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
        Schema::table('credits', function (Blueprint $table) {
            $table->dropColumn('montant_restant');
            $table->dropColumn('date_limite');
        });
    }
}
