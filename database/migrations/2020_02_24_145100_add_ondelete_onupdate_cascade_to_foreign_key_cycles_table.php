<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyCyclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cycles', function(Blueprint $table)
		{
            $table->dropForeign('fk_cycles_associations_id');
			$table->foreign('associations_id', 'fk_cycles_associations_id')->references('id')->on('associations')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cycles', function(Blueprint $table)
		{
			$table->dropForeign('fk_cycles_associations_id');
		});
    }
}
