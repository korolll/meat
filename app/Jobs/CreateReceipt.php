<?php

namespace App\Jobs;

use App\Services\Management\Receipt\Contracts\ReceiptFactoryContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateReceipt implements ShouldQueue
{
    use Dispatchable;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param \App\Services\Management\Receipt\Contracts\ReceiptFactoryContract $factory
     * @throws \App\Exceptions\TealsyException
     */
    public function handle(ReceiptFactoryContract $factory)
    {
        $factory->create($this->attributes);
    }
}
