<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToTransfertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('transferts', function(Blueprint $table)
		{
			$table->foreign('expediteur', 'fk_transferts_expediteur')->references('id')->on('comptes');
			$table->foreign('recepteur', 'fk_transferts_recepteur')->references('id')->on('comptes');
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

        Schema::table('transferts', function(Blueprint $table)
		{
			$table->dropForeign('fk_transferts_expediteur');
			$table->dropForeign('fk_transferts_recepteur');
		});
    }
}
