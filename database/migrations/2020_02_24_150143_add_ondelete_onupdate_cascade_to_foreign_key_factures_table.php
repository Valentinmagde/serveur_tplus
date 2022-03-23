<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyFacturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factures', function(Blueprint $table)
		{
            $table->dropForeign('fk_facture_cycles_id');
			$table->foreign('cycles_id', 'fk_facture_cycles_id')->references('id')->on('cycles')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factures', function(Blueprint $table)
		{
			$table->dropForeign('fk_facture_cycles_id');
		});
    }
}
