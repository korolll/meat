<?php

namespace App\Services\Management\Receipt\Contracts;

interface ReceiptFactoryContract
{
    /**
     * @param array $attributes
     * @return \App\Models\Receipt
     * @throws \App\Exceptions\TealsyException
     */
    public function create(array $attributes);
}
