<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTypeAssistanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('type_assistance', function(Blueprint $table)
		{
            $table->integer('max')->default(0);
            $table->integer('max_cycle')->default(0);
            $table->string('description', 200)->nullable();

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
        Schema::table('type_assistance', function (Blueprint $table) {
            $table->dropColumn('max');
            $table->dropColumn('max_cycle');
            $table->dropColumn('description');
        });
    }
}
