<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('user_type_id', 25);
            $table->string('full_name');
            $table->string('legal_form_id', 25);
            $table->string('organization_name');
            $table->text('organization_address');
            $table->text('address');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password');
            $table->string('inn', 12);
            $table->string('kpp', 9);
            $table->string('ogrn', 13);
            $table->string('user_verify_status_id', 25);
            $table->boolean('is_email_verified');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('user_type_id');
            $table->index('legal_form_id');
            $table->index('user_verify_status_id');
            $table->index('is_email_verified');
            $table->index('created_at');

            $table->foreign('user_type_id')->references('id')->on('user_types');
            $table->foreign('legal_form_id')->references('id')->on('legal_forms');
            $table->foreign('user_verify_status_id')->references('id')->on('user_verify_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
