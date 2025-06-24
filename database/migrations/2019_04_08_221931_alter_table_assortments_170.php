<?php

use App\Models\Assortment;
use Illuminate\Database\Migrations\Migration;

class AlterTableAssortments170 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * ->change() double поля падает с ошибкой Doctrine\DBAL\DBALException  : Unknown column type "double" requested.
         * поэтому raw запрос
         */
        DB::unprepared("ALTER TABLE assortments ALTER COLUMN volume DROP NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Assortment::whereNull('volume')->update(['volume' => 0]);
        DB::unprepared("ALTER TABLE assortments ALTER COLUMN volume SET NOT NULL;");
    }
}
