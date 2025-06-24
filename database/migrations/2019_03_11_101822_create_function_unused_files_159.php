<?php

use Illuminate\Database\Migrations\Migration;

class CreateFunctionUnusedFiles159 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        DB::unprepared("
            CREATE FUNCTION unused_files() RETURNS SETOF files AS
            $$
            DECLARE
              sql text[];
              tbl text;
              col text;
            BEGIN
              FOR tbl, col IN
                SELECT table_constraints.table_name,
                       key_column_usage.column_name
                FROM information_schema.table_constraints
                       JOIN information_schema.key_column_usage USING (constraint_name, table_schema)
                       JOIN information_schema.constraint_column_usage USING (constraint_name, table_schema)
                WHERE table_constraints.constraint_type = 'FOREIGN KEY'
                  AND constraint_column_usage.table_name = 'files'
                  AND constraint_column_usage.column_name = 'uuid'
                LOOP
                  sql := array_append(sql, format('SELECT %s FROM %s WHERE %s IS NOT NULL', col, tbl, col));
                END LOOP;

              RETURN QUERY EXECUTE format('SELECT * FROM files WHERE uuid NOT IN (%s)', array_to_string(sql, ' UNION '));
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS unused_files;');
    }
}
