<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operations', function(Blueprint $table)
		{
            $table->dropForeign('fk_operations_membre_id');
			$table->foreign('membre_id', 'fk_operations_membre_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('operations', function(Blueprint $table)
		{
			$table->dropForeign('fk_operations_membre_id');
		});
    }
}
