<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAppContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_contact', function (Blueprint $table) {
            $table->string('id', 2)->primary();
            $table->string('email')->nullable();
            $table->string('call_center_number')->nullable();
            $table->string('social_network_instagram')->nullable();
            $table->string('social_network_vk')->nullable();
            $table->string('social_network_facebook')->nullable();
            $table->string('social_messenger_telegram')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_contact');
    }
}
