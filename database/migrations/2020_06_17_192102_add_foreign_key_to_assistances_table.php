<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToAssistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('assistances', function(Blueprint $table)
		{
            $table->foreign('echeances_id', 'fk_assistances_echeances_id')->references('id')->on('echeanciers')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('assistances', function(Blueprint $table)
		{
			$table->dropForeign('fk_assistances_echeances_id');
		});
    }
}
