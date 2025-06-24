<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoyaltyCodesTable extends Migration
{
    public function up()
    {
        Schema::create('loyalty_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_uuid')->index();
            $table->uuid('code');
            $table->timestamp('expires_on');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loyalty_codes');
    }
}
