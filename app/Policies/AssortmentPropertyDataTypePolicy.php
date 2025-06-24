<?php

namespace App\Policies;

use App\Models\AssortmentPropertyDataType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssortmentPropertyDataTypePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * @param User $user
     * @param AssortmentPropertyDataType $assortmentPropertyDataType
     * @return bool
     */
    public function view(User $user, AssortmentPropertyDataType $assortmentPropertyDataType)
    {
        return true;
    }
}
