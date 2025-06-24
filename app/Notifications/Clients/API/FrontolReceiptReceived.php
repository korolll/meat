<?php

namespace App\Notifications\Clients\API;

use App\Models\Receipt;
use App\Notifications\API\CustomNotification;

class FrontolReceiptReceived extends CustomNotification
{
    /**
     * @param \App\Models\Receipt $receipt
     *
     * @return self
     */
    public static function create(Receipt $receipt)
    {
        $receiptNumber = 'N' . $receipt->receipt_package_id;
        return new static(
            "Ваш чек $receiptNumber",
            "Получен чек $receiptNumber",
            [
                'type' => 'receipts',
                'id' => $receipt->uuid
            ]
        );
    }
}
