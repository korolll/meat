<?php

namespace App\Contracts\Models\Counter;

interface IncrementCounterContract
{
    /**
     * @param string $key
     * @return float
     */
    public function increment(string $key): float;
}
