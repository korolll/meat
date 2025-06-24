<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssortmentBarcodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assortment_barcodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('assortment_uuid');
            $table->string('barcode', 15)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();

            $table->timestampsTz();

            $table->foreign('assortment_uuid')->references('uuid')->on('assortments');
        });

        $this->migrateBarcodes();

        Schema::table('assortments', function (Blueprint $table) {
            $table->string('barcode', 13)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assortment_barcodes');

        Schema::table('assortments', function (Blueprint $table) {
            $table->string('barcode', 13)->nullable(false)->change();
        });
    }

    protected function migrateBarcodes()
    {
        DB::insert(<<<SQL
WITH assortments_data AS (
    SELECT DISTINCT
        uuid assortment_uuid,
        barcode,
        created_at,
        created_at started_at,
        COALESCE(deleted_at) finished_at,
        COALESCE(deleted_at) ISNULL is_active
    FROM assortments
)
INSERT INTO assortment_barcodes (assortment_uuid, barcode, created_at, started_at, finished_at, is_active)
    SELECT * FROM assortments_data;
SQL
        );
    }
}
