<?php

use App\Models\FileCategory;
use Illuminate\Database\Migrations\Migration;

class InsertIntoFileCategoriesCatalogImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('file_categories')
            ->insert([
                [
                    'id' => FileCategory::ID_CATALOG_IMAGE,
                    'name' => 'Изображение каталога',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::table('file_categories')->delete(FileCategory::ID_CATALOG_IMAGE);
    }
}
