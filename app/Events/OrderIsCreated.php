<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderIsCreated extends EventWithMoment
{
    use Dispatchable, SerializesModels;

    public Order $order;

    /**
     * @param \App\Models\Order $order=
     */
    public function __construct(Order $order)
    {
        parent::__construct();
        $this->order = $order;
    }
}
