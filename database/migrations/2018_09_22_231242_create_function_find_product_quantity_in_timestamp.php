<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFunctionFindProductQuantityInTimestamp extends Migration
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
            CREATE OR REPLACE FUNCTION find_product_quantity_in_timestamp(uuid, timestamp with time zone) RETURNS integer AS $$ 
            DECLARE quantity integer;
            BEGIN
                SELECT quantity_new INTO quantity
                FROM warehouse_transactions
                WHERE product_uuid = $1 AND created_at <= $2
                ORDER BY warehouse_transactions.uuid DESC
                LIMIT 1;
                
                IF (quantity is NULL) THEN 
                    quantity = 0;
                END IF;
                
                RETURN quantity;
            END; 
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS find_product_quantity_in_timestamp;');
    }
}
