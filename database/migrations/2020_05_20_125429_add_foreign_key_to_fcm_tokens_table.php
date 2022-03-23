<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToFcmTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    //
        Schema::table('fcm_tokens', function(Blueprint $table)
		{
            $table->foreign('utilisateurs_id', 'fk_fcm_tokens_utilisateurs_id')->references('id')->on('utilisateurs')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('fcm_tokens', function(Blueprint $table)
		{
			$table->dropForeign('fk_fcm_tokens_utilisateurs_id');
		});
    }
}
