<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannerImageForFileCategories extends Migration
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
                    'id' => \App\Models\FileCategory::ID_BANNER_IMAGE,
                    'name' => 'Изображение истории',
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
        \Illuminate\Support\Facades\DB::table('file_categories')
            ->whereIn('id', [
                \App\Models\FileCategory::ID_BANNER_IMAGE,
            ])
            ->delete();
    }
}
