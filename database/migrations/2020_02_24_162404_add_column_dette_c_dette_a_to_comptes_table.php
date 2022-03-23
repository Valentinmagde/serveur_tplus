<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDetteCDetteAToComptesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->decimal('dette_c', 15,3)->nullable();
            $table->decimal('dette_a', 15,3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comptes', function(Blueprint $table)
		{
			$table->dropColumn('dette_c');
			$table->dropColumn('dette_a');
		});
    }
}
