<?php

namespace App\Services\Models\User;

use App\Models\User;
use App\Models\UserDeliveryZone;

class UserUpdater implements UserUpdaterInterface
{
    /**
     * @param \App\Models\User $user
     * @param array            $properties
     * @param array            $emails
     * @param array            $files
     *
     * @return \App\Models\User
     */
    public function update(User $user, array $properties, array $emails, array $files): User
    {
        $zoneData = UserDeliveryZoneHelper::extractZoneData($properties);

        if ($zoneData) {
            $deliveryZone = UserDeliveryZoneHelper::findExistingZone($zoneData);
            if (!$deliveryZone) {
                $deliveryZone = UserDeliveryZone::create($zoneData);
            }
            $user->user_delivery_zone_id = $deliveryZone->id;
        }

        $user->fill($properties);
        $user->save();

        $this->syncAdditionalEmails($user, $emails);
        $this->syncFiles($user, $files);

        return $user;
    }


    /**
     * @param \App\Models\User $user
     * @param array            $additionalEmails
     */
    protected function syncAdditionalEmails(User $user, array $additionalEmails): void
    {
        $user->userAdditionalEmails()->delete();

        foreach ($additionalEmails as $email) {
            $user->userAdditionalEmails()->create(
                compact('email')
            );
        }
    }

    /**
     * @param \App\Models\User $user
     * @param array            $files
     */
    protected function syncFiles(User $user, array $files): void
    {
        $user->files()->sync($files);
    }
}
