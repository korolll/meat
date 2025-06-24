<?php

use Illuminate\Database\Migrations\Migration;
use \App\Services\Framework\Database\Migrations\ManagesComments;

class AlterTableCatalogs142 extends Migration
{
    use ManagesComments;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->commentOnColumn('catalogs', [
            'assortments_count' => 'Количество подтвержденных номенклатур, включая под каталоги',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->commentOnColumn('catalogs', [
            'assortments_count' => 'Количество номенклатур, включая под каталоги',
        ]);
    }
}
