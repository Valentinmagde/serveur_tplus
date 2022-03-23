<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypeSanctionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_sanctions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('associations_id')->index('fk_type_sanctions_associations_id');
            $table->string('nom');
            $table->integer('montant');
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
        Schema::dropIfExists('type_sanction');
    }
}
