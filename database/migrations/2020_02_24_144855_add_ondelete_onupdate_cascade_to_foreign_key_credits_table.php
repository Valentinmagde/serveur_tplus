<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credits', function(Blueprint $table)
		{
            $table->dropForeign('fk_credits_membres_id');
			$table->dropForeign('fk_credits_mutuelles_activites_id');
			$table->dropForeign('fk_credits_mutuelles_id');
			$table->foreign('membres_id', 'fk_credits_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('mutuelles_activites_id', 'fk_credits_mutuelles_activites_id')->references('id')->on('activites')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('mutuelles_id', 'fk_credits_mutuelles_id')->references('id')->on('mutuelles')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credits', function(Blueprint $table)
		{
			$table->dropForeign('fk_credits_membres_id');
			$table->dropForeign('fk_credits_mutuelles_activites_id');
			$table->dropForeign('fk_credits_mutuelles_id');
		});
    }
}
