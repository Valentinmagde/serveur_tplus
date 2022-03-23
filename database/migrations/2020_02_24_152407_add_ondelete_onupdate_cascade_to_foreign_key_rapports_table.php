<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyRapportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rapports', function(Blueprint $table)
		{
            $table->dropForeign('fk_rapport_AGs_id');
			$table->foreign('AGs_id', 'fk_rapport_AGs_id')->references('id')->on('ags')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rapports', function(Blueprint $table)
		{
			$table->dropForeign('fk_rapport_AGs_id');
		});
    }
}
