<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUsers168 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signer_type_id', 25)->nullable()->comment('Идентификатор подписанта');
            $table->string('signer_full_name')->nullable()->comment('ФИО подписанта');

            $table->string('power_of_attorney_number')->nullable()->comment('Номер доверенности');
            $table->timestampTz('date_of_power_of_attorney')->nullable()->comment('Дата выдачи доверенности');

            $table->string('ip_registration_certificate_number')->nullable()->comment('№ свидетельства о регистрации ИП');
            $table->timestampTz('date_of_ip_registration_certificate')->nullable()->comment('Дата выдачи видетельства о регистрации ИП');

            $table->index('signer_type_id');
            $table->foreign('signer_type_id')->references('id')->on('signer_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'signer_type_id',
                'signer_full_name',
                'power_of_attorney_number',
                'date_of_power_of_attorney',
                'ip_registration_certificate_number',
                'date_of_ip_registration_certificate',
            ]);
        });
    }
}
