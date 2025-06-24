<?php

namespace App\Policies;

use App\Models\AssortmentBrand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AssortmentBrandPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index()
    {
        return true;
    }

    /**
     * @param User $user
     * @param AssortmentBrand $assortmentBrand
     * @return bool
     */
    public function view(User $user, AssortmentBrand $assortmentBrand)
    {
        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin || $user->is_supplier;
    }

    /**
     * @param User $user
     * @param AssortmentBrand $assortmentBrand
     * @return bool
     */
    public function update(User $user, AssortmentBrand $assortmentBrand)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param AssortmentBrand $assortmentBrand
     * @return bool
     */
    public function delete(User $user, AssortmentBrand $assortmentBrand)
    {
        return $user->is_admin;
    }
}
