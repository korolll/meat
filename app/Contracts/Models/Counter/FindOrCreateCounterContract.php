<?php

namespace App\Contracts\Models\Counter;

use App\Models\Counter;

interface FindOrCreateCounterContract
{
    /**
     * @param string $name
     * @param array $attributes
     * @return Counter
     */
    public function findOrCreate(string $name, array $attributes = []): Counter;
}
