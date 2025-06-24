<?php

namespace App\Services\Models\Counter;

use App\Contracts\Models\Counter\FindOrCreateCounterContract;
use App\Contracts\Models\Counter\IncrementCounterContract;
use App\Models\Counter;

class IncrementCounter implements IncrementCounterContract
{
    /**
     * @var FindOrCreateCounterContract
     */
    protected $finder;

    /**
     * IncrementCounter constructor.
     * @param FindOrCreateCounterContract $finder
     */
    public function __construct(FindOrCreateCounterContract $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param string $key
     * @return float
     */
    public function increment(string $key): float
    {
        $counter = $this->finder->findOrCreate($key);
        Counter::query()->where('id', $counter->id)->increment('value', $counter->step);
        return $counter->value + $counter->step;
    }
}
