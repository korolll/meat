<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePromoDescriptionsAddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promo_descriptions', function (Blueprint $table) {
            $table->string('discount_type')->nullable()->unique();
            $table->string('color')->nullable();
            $table->boolean('is_hidden')->default(false);

            $table->uuid('logo_file_uuid')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promo_descriptions', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'color',
                'is_hidden',
            ]);

            $table->uuid('logo_file_uuid')->change();
        });
    }
}
