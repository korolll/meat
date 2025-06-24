<?php

use Illuminate\Database\Migrations\Migration;

class AlterTriggerCurrentPriceListChanged145 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->dropTriggerAndFunction();

        DB::unprepared("
            CREATE FUNCTION current_price_list_changed() RETURNS trigger AS
            $$ BEGIN
                UPDATE price_lists SET price_list_status_id = 'archive', date_till = NEW.updated_at 
                WHERE 
                    price_lists.user_uuid = OLD.user_uuid 
                    AND price_lists.price_list_status_id = 'current' 
                    AND price_lists.uuid <> NEW.uuid
                    AND (CASE WHEN OLD.customer_user_uuid IS NULL THEN price_lists.customer_user_uuid IS NULL ELSE price_lists.customer_user_uuid = OLD.customer_user_uuid END);
                
                UPDATE products SET price = price_list_product.price_new 
                FROM price_list_product 
                JOIN price_lists ON price_lists.uuid = price_list_product.price_list_uuid AND price_lists.customer_user_uuid IS NULL
                WHERE 
                    price_list_product.price_list_uuid = NEW.uuid 
                    AND price_list_product.product_uuid = products.uuid;
                RETURN NEW;
            END; $$
            LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER current_price_list_changed AFTER UPDATE OF price_list_status_id ON price_lists
                FOR EACH ROW WHEN (NEW.price_list_status_id = 'current') EXECUTE PROCEDURE current_price_list_changed();
        ");
    }

    protected function dropTriggerAndFunction()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS current_price_list_changed ON price_lists;');
        DB::unprepared('DROP FUNCTION IF EXISTS current_price_list_changed;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropTriggerAndFunction();
        DB::unprepared("
            CREATE FUNCTION current_price_list_changed() RETURNS trigger AS
            $$ BEGIN
                UPDATE price_lists SET price_list_status_id = 'archive', date_till = NEW.updated_at WHERE price_lists.user_uuid = OLD.user_uuid AND price_lists.price_list_status_id = 'current' AND price_lists.uuid <> NEW.uuid;
                UPDATE products SET price = price_list_product.price_new FROM price_list_product WHERE price_list_product.price_list_uuid = NEW.uuid AND price_list_product.product_uuid = products.uuid;
                RETURN NEW;
            END; $$
            LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER current_price_list_changed AFTER UPDATE OF price_list_status_id ON price_lists
                FOR EACH ROW WHEN (NEW.price_list_status_id = 'current') EXECUTE PROCEDURE current_price_list_changed();
        ");
    }
}
