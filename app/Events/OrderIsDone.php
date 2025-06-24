<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderIsDone extends EventWithMoment
{
    use Dispatchable, SerializesModels;

    public Order $order;
    public string $oldStatusId;

    /**
     * @param \App\Models\Order $order
     * @param string            $oldStatusId
     */
    public function __construct(Order $order, string $oldStatusId)
    {
        parent::__construct();
        $this->order = $order;
        $this->oldStatusId = $oldStatusId;
    }
}
