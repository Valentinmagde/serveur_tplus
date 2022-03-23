<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnNumRecuAndCommentaireToOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('operations', function($table) {
            $table->integer('num_recu')->nullable();
            $table->string('commentaire', 500)->nullable();
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
        Schema::table('operations', function($table) {
            $table->dropColumn('num_recu')->nullable();
            $table->dropColumn('commentaire')->nullable();
        });
    }
}
