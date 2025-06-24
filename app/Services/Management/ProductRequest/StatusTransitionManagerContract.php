<?php

namespace App\Services\Management\ProductRequest;

use App\Models\ProductRequest;

interface StatusTransitionManagerContract
{
    /**
     * @param string $statusAttribute
     * @param string $nextStatusId
     * @param array $additionalAttributes
     * @return ProductRequest
     * @throws \App\Exceptions\TealsyException
     */
    public function transition($statusAttribute, $nextStatusId, $additionalAttributes = []);
}
