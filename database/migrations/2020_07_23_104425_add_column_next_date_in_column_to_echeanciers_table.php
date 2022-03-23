<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnNextDateInColumnToEcheanciersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('echeanciers', function(Blueprint $table)
		{
            $table->integer('next_date_in')->nullable();

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
        Schema::table('echeanciers', function(Blueprint $table)
		{
            $table->dropColumn('next_date_in');

        });
    }
}
