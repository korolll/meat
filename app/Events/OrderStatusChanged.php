<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends EventWithMoment
{
    use Dispatchable, SerializesModels;

    public Order $order;
    public string $oldStatusId;
    public string $newStatusId;

    /**
     * @param \App\Models\Order $order
     * @param string            $oldStatusId
     * @param string            $newStatusId
     */
    public function __construct(Order $order, string $oldStatusId, string $newStatusId)
    {
        parent::__construct();
        $this->order = $order;
        $this->oldStatusId = $oldStatusId;
        $this->newStatusId = $newStatusId;
    }
}
