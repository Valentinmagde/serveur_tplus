<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAttributionCagnoteToTontineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tontines', function (Blueprint $table) {
            $table->string('attribution_cagnote')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tontines', function (Blueprint $table) {
            $table->dropColumn('attribution_cagnote');
        });
    }
}
