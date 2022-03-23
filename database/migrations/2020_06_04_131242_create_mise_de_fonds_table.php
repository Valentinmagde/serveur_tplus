<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiseDeFondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mise_de_fonds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('membres_id')->index('fk_mise_de_fonds_membres_id');
            $table->integer('mutuelles_id')->index('fk_mise_de_fonds_mutuelles_id');
            $table->decimal('montant', 15,3)->default(0);
            $table->integer('date_versement')->nullable();
            $table->integer('date_created')->nullable();
            $table->integer('date_updated')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mise_de_fonds');
    }
}
