<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLieuFixeAgToCycleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('cycles', function (Blueprint $table) {
            $table->string('lieu_fixe_ag',191)->nullable();
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
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn('lieu_fixe_ag');
        });
    }
}
