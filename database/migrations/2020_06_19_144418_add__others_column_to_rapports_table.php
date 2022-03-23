<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOthersColumnToRapportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('rapports', function(Blueprint $table)
		{
            $table->string('hote');
            $table->string('presidence');
            $table->string('secretaire');
            $table->string('lieu');
            $table->integer('date_effective');

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
        Schema::table('rapports', function (Blueprint $table) {
            $table->dropColumn('hote');
            $table->dropColumn('presidence');
            $table->dropColumn('secretaire');
            $table->dropColumn('lieu');
            $table->dropColumn('date_effective');
        });
    }
}
