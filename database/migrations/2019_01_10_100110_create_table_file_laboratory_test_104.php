<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFileLaboratoryTest104 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_laboratory_test', function (Blueprint $table) {
            $table->uuid('laboratory_test_uuid');
            $table->uuid('file_uuid');
            $table->string('file_category_id', 25);
            $table->string('public_name')->nullable();

            $table->primary(['laboratory_test_uuid', 'file_uuid']);
            $table->index('laboratory_test_uuid');
            $table->index('file_uuid');
            $table->index('file_category_id');

            $table->foreign('laboratory_test_uuid')->references('uuid')->on('laboratory_tests');
            $table->foreign('file_uuid')->references('uuid')->on('files');
            $table->foreign('file_category_id')->references('id')->on('file_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_laboratory_test');
    }
}
