<?php

namespace App\Policies;

use App\Models\PaymentVendorSetting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentVendorSettingPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param PaymentVendorSetting $paymentVendorSetting
     * @return bool
     */
    public function view(User $user, PaymentVendorSetting $paymentVendorSetting)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param User $user
     * @param PaymentVendorSetting $paymentVendorSetting
     * @return bool
     */
    public function update(User $user, PaymentVendorSetting $paymentVendorSetting)
    {
        return $user->is_admin;
    }
}
