<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUtilisateursHasCategoriesNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('utilisateurs_has_categories_notifications', function (Blueprint $table) {
            $table->integer('utilisateurs_id')->index('fk_uhcn_utilisateurs_id');
            $table->integer('categories_notifications_id')->index('fk_uhcn_categories_notifications_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('utilisateurs_has_categories_notifications');
    }
}
