<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyInvitationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('invitations', function(Blueprint $table)
		{
            $table->foreign('associations_id', 'fk_invitations_associations_id')->references('id')->on('associations')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('membres_id', 'fk_invitations_membres_id')->references('id')->on('membres')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('invitations', function(Blueprint $table)
		{
			$table->dropForeign('fk_invitations_associations_id');
			$table->dropForeign('fk_invitations_membres_id');
		});
    }
}
