<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaymentTypeChanging extends EventWithMoment
{
    use Dispatchable, SerializesModels;

    public Order $order;
    public string $oldTypeId;
    public string $newTypeId;

    /**
     * @param \App\Models\Order $order
     * @param string            $oldTypeId
     * @param string            $newTypeId
     */
    public function __construct(Order $order, string $oldTypeId, string $newTypeId)
    {
        parent::__construct();
        $this->order = $order;
        $this->oldTypeId = $oldTypeId;
        $this->newTypeId = $newTypeId;
    }
}
