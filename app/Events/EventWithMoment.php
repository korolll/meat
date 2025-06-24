<?php

namespace App\Events;

use Carbon\CarbonInterface;

abstract class EventWithMoment
{
    public CarbonInterface $moment;

    /**
     *
     */
    public function __construct()
    {
        $this->moment = now();
    }
}
