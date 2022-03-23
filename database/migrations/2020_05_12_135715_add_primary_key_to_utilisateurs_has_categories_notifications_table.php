<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrimaryKeyToUtilisateursHasCategoriesNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('utilisateurs_has_categories_notifications', function (Blueprint $table) {
            $table->primary(['utilisateurs_id','categories_notifications_id'])->index('uhcn_primary_key');
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

        Schema::drop('utilisateurs_has_categories_notifications');
    }
}
