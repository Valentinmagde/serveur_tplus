<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToActivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('activites', function(Blueprint $table)
		{
            $table->foreign('cycles_id', 'fk_activites_cycles_id')->references('id')->on('cycles')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('activites', function(Blueprint $table)
		{
			$table->dropForeign('fk_cactivites_cycles_id');
		});
    }
}
