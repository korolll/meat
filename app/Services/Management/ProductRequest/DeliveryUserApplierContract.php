<?php

namespace App\Services\Management\ProductRequest;

use App\Models\ProductRequest;
use App\Models\User;

interface DeliveryUserApplierContract
{
    /**
     * @param User $user
     * @param null|string $comment
     * @return ProductRequest
     * @throws \App\Exceptions\TealsyException
     */
    public function apply(User $user, ?string $comment = null);

    /**
     * @param null|string $comment
     * @return ProductRequest
     */
    public function reset(?string $comment = null);
}
