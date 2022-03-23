<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyCommentaireNouvellesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commentaire_nouvelles', function(Blueprint $table)
		{
            $table->dropForeign('fk_membre_membres_id');
			$table->dropForeign('fk_nouvelle_nouvelles_id');
			$table->foreign('membres_id', 'fk_membre_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('nouvelles_id', 'fk_nouvelle_nouvelles_id')->references('id')->on('nouvelles')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commentaire_nouvelles', function(Blueprint $table)
		{
			$table->dropForeign('fk_membre_membres_id');
			$table->dropForeign('fk_nouvelle_nouvelles_id');
		});
    }
}
