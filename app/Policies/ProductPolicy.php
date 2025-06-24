<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
     * @param Product $product
     * @return bool
     */
    public function view(User $user, Product $product)
    {
        return $product->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_distribution_center || $user->is_supplier || $user->is_store;
    }

    /**
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function update(User $user, Product $product)
    {
        return $product->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function destroy(User $user, Product $product)
    {
        return $product->user_uuid === $user->uuid;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function reportProductsSummaryIndex(User $user)
    {
        return $user->is_store;
    }

    /**
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function reportProductsSummaryView(User $user, Product $product)
    {
        return $user->is_store && $product->user_uuid === $user->uuid;
    }
}
