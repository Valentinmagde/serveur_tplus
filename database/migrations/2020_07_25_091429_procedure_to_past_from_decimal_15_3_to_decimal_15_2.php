<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProcedureToPastFromDecimal153ToDecimal152 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        DB::unprepared("

        drop procedure if exists changeIntDefaultValue;
        create procedure changeDecimalTo15comma2()
        begin
            declare done int default false;
            declare tablename CHAR(255);
            declare columnname CHAR(255);
            declare cur cursor for SELECT  TABLE_NAME, COLUMN_NAME
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    where TABLE_SCHEMA = 'tontine'
                                    and DATA_TYPE like '%decimal%';
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = true;
            open cur;
        
            myloop: loop
                fetch cur into tablename, columnname;
                if done then
                    leave myloop;
                end if;
                set @sql = CONCAT('alter table `',tablename,'` modify column ', columnname,' decimal(15,2)');
                prepare stmt from @sql;
                execute stmt;
                drop prepare stmt;
            end loop;
            close cur;
        end ");

        DB::raw('call changeDecimalTo15comma2()');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
