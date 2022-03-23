<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyNouvellesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nouvelles', function(Blueprint $table)
		{
            $table->dropForeign('fk_nouvelles_associations_id');
			$table->dropForeign('fk_nouvelles_membres_id');
			$table->foreign('associations_id', 'fk_nouvelles_associations_id')->references('id')->on('associations')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('membres_id', 'fk_nouvelles_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nouvelles', function(Blueprint $table)
		{
			$table->dropForeign('fk_nouvelles_associations_id');
			$table->dropForeign('fk_nouvelles_membres_id');
		});
    }
}
