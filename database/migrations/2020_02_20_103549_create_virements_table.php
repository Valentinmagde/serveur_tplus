<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virements', function (Blueprint $table) {
            $table->integer('id',true);
            $table->integer('comptes_id')->index('fk_virements_comptes_id');
            $table->integer('activites_id')->index('fk_virements_activites_id');
            $table->string('type', 45);
            $table->decimal('montant', 15,3);
            $table->string('created_by', 191);
            $table->integer('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('virements');
    }
}
