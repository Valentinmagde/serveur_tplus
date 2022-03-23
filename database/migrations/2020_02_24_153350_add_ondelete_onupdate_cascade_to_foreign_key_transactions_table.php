<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOndeleteOnupdateCascadeToForeignKeyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function(Blueprint $table)
		{
            $table->dropForeign('fk_transaction_comptes_id');
			$table->dropForeign('fk_transaction_operations_id');
			$table->foreign('comptes_id', 'fk_transaction_comptes_id')->references('id')->on('comptes')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('operations_id', 'fk_transaction_operations_id')->references('id')->on('operations')->onUpdate('cascade')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function(Blueprint $table)
		{
			$table->dropForeign('fk_transaction_comptes_id');
			$table->dropForeign('fk_transaction_operations_id');
		});
    }
}
