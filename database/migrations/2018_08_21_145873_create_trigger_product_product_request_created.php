<?php

use Illuminate\Database\Migrations\Migration;

class CreateTriggerProductProductRequestCreated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        // Обновляет суммарные показатели в связанной заявке
        DB::unprepared("
            CREATE FUNCTION product_product_request_created() RETURNS trigger AS
            $$ BEGIN
                UPDATE
                    product_requests
                SET
                    price  = price  + NEW.price  * NEW.quantity,
                    weight = weight + NEW.weight * NEW.quantity,
                    volume = volume + NEW.volume * NEW.quantity
                WHERE
                    product_requests.uuid = NEW.product_request_uuid;

                RETURN NEW;
            END; $$
            LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER product_product_request_created AFTER INSERT ON product_product_request
                FOR EACH ROW EXECUTE PROCEDURE product_product_request_created();
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS product_product_request_created ON product_product_request;');
        DB::unprepared('DROP FUNCTION IF EXISTS product_product_request_created;');
    }
}
