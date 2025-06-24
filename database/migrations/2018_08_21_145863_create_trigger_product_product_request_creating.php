<?php

use Illuminate\Database\Migrations\Migration;

class CreateTriggerProductProductRequestCreating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        // Дополняет запись информацией о цене, весе и объеме товара
        DB::unprepared("
            CREATE FUNCTION product_product_request_creating() RETURNS trigger AS
            $$ BEGIN
                SELECT
                    products.price,
                    assortments.weight,
                    assortments.volume
                INTO
                    NEW.price,
                    NEW.weight,
                    NEW.volume
                FROM
                    assortments,
                    products
                WHERE
                        assortments.uuid = products.assortment_uuid
                    AND products.uuid = NEW.product_uuid;

                RETURN NEW;
            END; $$
            LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER product_product_request_creating BEFORE INSERT ON product_product_request
                FOR EACH ROW EXECUTE PROCEDURE product_product_request_creating();
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS product_product_request_creating ON product_product_request;');
        DB::unprepared('DROP FUNCTION IF EXISTS product_product_request_creating;');
    }
}
