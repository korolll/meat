<?php

namespace App\Policies\ProductRequests;

use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerProductRequestPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function indexOwned(User $user)
    {
        return $user->is_distribution_center || $user->is_store || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param ProductRequest $productRequest
     * @return bool
     */
    public function view(User $user, ProductRequest $productRequest)
    {
        return $productRequest->customer_user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_distribution_center || $user->is_store || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param ProductRequest $productRequest
     * @return bool
     */
    public function update(User $user, ProductRequest $productRequest)
    {
        return $productRequest->customer_user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param ProductRequest $productRequest
     * @return bool
     */
    public function setStatus(User $user, ProductRequest $productRequest)
    {
        return $productRequest->customer_user_uuid === $user->uuid;
    }
}
