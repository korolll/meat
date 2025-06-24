<?php

namespace App\Services\Models\Counter;

use App\Contracts\Models\Counter\FindOrCreateCounterContract;
use App\Models\Counter;

class FindOrCreateCounter implements FindOrCreateCounterContract
{
    /**
     * @param string $key
     * @param array $attributes
     * @return Counter
     */
    public function findOrCreate(string $key, array $attributes = []): Counter
    {
        return Counter::query()
            ->firstOrCreate([
                'id' => $key
            ], $attributes);
    }
}
