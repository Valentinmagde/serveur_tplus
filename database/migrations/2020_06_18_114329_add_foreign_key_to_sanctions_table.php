<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToSanctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sanctions', function(Blueprint $table)
		{
            $table->foreign('membres_id', 'fk_sanctions_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('ags_id', 'fk_sanctions_ags_id')->references('id')->on('ags')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('sanctions', function(Blueprint $table)
		{
			$table->dropForeign('fk_sanctions_membres_id');
			$table->dropForeign('fk_sanctions_ags_id');
		});
    }
}
