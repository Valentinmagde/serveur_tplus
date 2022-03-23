<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyAgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ags', function(Blueprint $table)
		{
            $table->dropForeign('fk_AGs_cycles_id');
			$table->dropForeign('fk_AGs_membres_id');
			$table->foreign('cycles_id', 'fk_AGs_cycles_id')->references('id')->on('cycles')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('membres_id', 'fk_AGs_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ags', function(Blueprint $table)
		{
			$table->dropForeign('fk_AGs_cycles_id');
			$table->dropForeign('fk_AGs_membres_id');
		});
    }
}
