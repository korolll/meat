<?php

namespace App\Policies\ProductRequests;

use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierProductRequestPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function indexOwned(User $user)
    {
        return $user->is_distribution_center || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param ProductRequest $productRequest
     * @return bool
     */
    public function view(User $user, ProductRequest $productRequest)
    {
        return $productRequest->supplier_user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param ProductRequest $productRequest
     * @return bool
     */
    public function setStatus(User $user, ProductRequest $productRequest)
    {
        return $productRequest->supplier_user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param ProductRequest $productRequest
     * @return bool
     */
    public function setConfirmedDate(User $user, ProductRequest $productRequest)
    {
        return $productRequest->supplier_user_uuid === $user->uuid;
    }
}
