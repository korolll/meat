<?php

namespace App\Policies;

use App\Models\DiscountForbiddenCatalog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiscountForbiddenCatalogPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User                     $user
     * @param DiscountForbiddenCatalog $catalog
     *
     * @return bool
     */
    public function view(User $user, DiscountForbiddenCatalog $catalog)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User                     $user
     * @param DiscountForbiddenCatalog $catalog
     *
     * @return bool
     */
    public function delete(User $user, DiscountForbiddenCatalog $catalog)
    {
        return $user->is_admin;
    }
}
