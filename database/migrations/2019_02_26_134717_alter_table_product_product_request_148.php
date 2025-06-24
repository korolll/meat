<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProductProductRequest148 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_product_request', function (Blueprint $table) {
            $table->integer('quantity_actual')->default(0)->comment('Фактическое количество');
        });

        \Illuminate\Support\Facades\DB::table('product_product_request')->update([
            'quantity_actual' => DB::raw('quantity'),
        ]);

        Schema::table('product_product_request', function (Blueprint $table) {
            $table->integer('quantity_actual')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_product_request', function (Blueprint $table) {
            $table->dropColumn('quantity_actual');
        });
    }
}
