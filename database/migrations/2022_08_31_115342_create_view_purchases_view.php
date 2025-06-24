<?php

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewPurchasesView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $orderMorph = Order::MORPH_TYPE_ALIAS;
        $receiptMorph = Receipt::MORPH_TYPE_ALIAS;

        $orderProductMorph = OrderProduct::MORPH_TYPE_ALIAS;
        $receiptLineMorph = ReceiptLine::MORPH_TYPE_ALIAS;

        $orderStatusDone = OrderStatus::ID_DONE;
        $sql = "
            CREATE OR REPLACE VIEW purchases_view AS 
                SELECT
                    '$orderMorph' as source,
                    order_products.order_uuid as source_id,
                    '$orderProductMorph' as source_line,
                    order_products.uuid as source_line_id,
                    order_products.product_uuid,
                    orders.client_uuid,
                    order_products.discountable_type,
                    order_products.discountable_uuid,
                    order_products.price_with_discount,
                    order_products.discount,
                    order_products.quantity,
                    order_products.total_bonus,
                    order_products.paid_bonus,
                    order_products.total_discount,
                    order_products.total_weight,
                    orders.created_at
                FROM order_products
                JOIN orders ON orders.uuid = order_products.order_uuid
                WHERE orders.order_status_id = '$orderStatusDone'
              UNION
                SELECT
                    '$receiptMorph' as source,
                    receipt_lines.receipt_uuid as source_id,
                    '$receiptLineMorph' as source_line,
                    receipt_lines.uuid as source_line_id,
                    receipt_lines.product_uuid,
                    loyalty_cards.client_uuid,
                    receipt_lines.discountable_type,
                    receipt_lines.discountable_uuid,
                    receipt_lines.price_with_discount,
                    receipt_lines.discount,
                    receipt_lines.quantity,
                    receipt_lines.total_bonus,
                    receipt_lines.paid_bonus,
                    NULL as total_discount,
                    NULL as total_weight,
                    receipts.created_at
                FROM receipt_lines
                JOIN receipts ON receipts.uuid = receipt_lines.receipt_uuid
                JOIN loyalty_cards ON loyalty_cards.uuid = receipts.loyalty_card_uuid
        ";

        Illuminate\Support\Facades\DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = "DROP VIEW IF EXISTS purchases_view";
        Illuminate\Support\Facades\DB::statement($sql);
    }
}
