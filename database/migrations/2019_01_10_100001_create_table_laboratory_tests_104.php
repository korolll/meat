<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLaboratoryTests104 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laboratory_tests', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('laboratory_test_appeal_type_uuid')->nullable();;
            $table->string('laboratory_test_status_id', 25);
            $table->uuid('customer_user_uuid');
            $table->uuid('executor_user_uuid')->nullable();
            $table->uuid('assortment_supplier_user_uuid')->nullable();

            // Кеш-поля
            $table->string('customer_full_name')->nullable();
            $table->string('customer_organization_name')->nullable();
            $table->text('customer_organization_address')->nullable();
            $table->string('customer_inn', 12)->nullable();
            $table->string('customer_ogrn', 15)->nullable();
            $table->string('customer_kpp', 9)->nullable();
            $table->string('customer_position')->nullable();
            $table->string('customer_bank_current_account', 20)->nullable();
            $table->string('customer_bank_correspondent_account', 20)->nullable();
            $table->string('customer_bank_name')->nullable();
            $table->string('customer_bank_identification_code', 9)->nullable();

            // Кеш поля ассортимента
            $table->string('assortment_barcode', 13)->nullable();
            $table->uuid('assortment_uuid')->nullable();
            $table->string('assortment_name')->nullable();
            $table->string('assortment_manufacturer')->nullable();
            $table->string('assortment_production_standard_id', 25)->nullable();

            // Дата/Номер патрии
            $table->string('batch_number')->nullable();

            // Параметры исследования
            $table->text('parameters')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('created_at');
            $table->index('laboratory_test_status_id');
            $table->index('laboratory_test_appeal_type_uuid');
            $table->index('customer_user_uuid');
            $table->index('executor_user_uuid');

            $table->foreign('customer_user_uuid')->references('uuid')->on('users');
            $table->foreign('executor_user_uuid')->references('uuid')->on('users');
            $table->foreign('assortment_supplier_user_uuid')->references('uuid')->on('users');
            $table->foreign('laboratory_test_status_id')->references('id')->on('laboratory_test_statuses');
            $table->foreign('laboratory_test_appeal_type_uuid')->references('uuid')->on('laboratory_test_appeal_types');
            $table->foreign('assortment_production_standard_id')->references('id')->on('production_standards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laboratory_tests');
    }
}
