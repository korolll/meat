<?php

namespace App\Events;

use App\Models\Receipt;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceiptReceived
{
    use Dispatchable, SerializesModels;

    /**
     * @var Receipt
     */
    public $receipt;

    /**
     * @param Receipt $receipt
     */
    public function __construct(Receipt $receipt)
    {
        $this->receipt = $receipt;
    }
}
