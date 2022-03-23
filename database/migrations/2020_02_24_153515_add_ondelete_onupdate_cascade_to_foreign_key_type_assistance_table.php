<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyTypeAssistanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('type_assistance', function(Blueprint $table)
		{
            $table->dropForeign('fk_type_assistance_associations_id');
			$table->foreign('associations_id', 'fk_type_assistance_associations_id')->references('id')->on('associations')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('type_assistance', function(Blueprint $table)
		{
			$table->dropForeign('fk_type_assistance_associations_id');
		});
    }
}
