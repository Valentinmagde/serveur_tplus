<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProcedureDefaultIntValue extends Migration
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
        create procedure changeIntDefaultValue()
        begin
            declare done int default false;
            declare tablename CHAR(255);
            declare columnname CHAR(255);
            declare cur cursor for SELECT  TABLE_NAME, COLUMN_NAME
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    where TABLE_SCHEMA = 'tontine'
                                    and DATA_TYPE like '%int%'
                                    and IS_NULLABLE = 'YES';
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = true;
            open cur;
        
            myloop: loop
                fetch cur into tablename, columnname;
                if done then
                    leave myloop;
                end if;
                set @sql = CONCAT('alter table `',tablename,'` alter ', columnname,' set default 0');
                prepare stmt from @sql;
                execute stmt;
                drop prepare stmt;
            end loop;
            close cur;
        end ");
        
        
        DB::raw('call changeIntDefaultValue()');
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //

        DB::unprepared('DROP PROCEDURE IF EXISTS changeIntDefaultValue');
    }
}
