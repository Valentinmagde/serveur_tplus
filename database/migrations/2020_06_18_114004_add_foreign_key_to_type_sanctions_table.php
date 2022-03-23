<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToTypeSanctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('type_sanctions', function(Blueprint $table)
		{
            $table->foreign('associations_id', 'fk_type_sanctions_associations_id')->references('id')->on('associations')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('type_sanctions', function(Blueprint $table)
		{
			$table->dropForeign('fk_type_sanctions_associations_id');
		});
    }
}
