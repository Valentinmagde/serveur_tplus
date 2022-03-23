<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePresenceEvenementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presence_evenements', function (Blueprint $table) {
            $table->integer('membres_id')->index('fk_presence_evenements_membres_id');
            $table->integer('evenements_id')->index('fk_presence_evenements_evenements_id');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presence_evenement');
    }
}
