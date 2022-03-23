<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyAvalistesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avalistes', function(Blueprint $table)
		{
            $table->dropForeign('fk_avalistes_membres_id1');
			$table->dropForeign('fk_avalistes_membres_id2');
			$table->foreign('membres_id1', 'fk_avalistes_membres_id1')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('membres_id2', 'fk_avalistes_membres_id2')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avalistes', function(Blueprint $table)
		{
			$table->dropForeign('fk_avalistes_membres_id1');
			$table->dropForeign('fk_avalistes_membres_id2');
		});
    }
}
