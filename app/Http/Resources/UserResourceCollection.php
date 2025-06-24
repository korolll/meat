<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;

class UserResourceCollection extends ResourceCollection
{
    /**
     * @param User $user
     * @return array
     */
    public function resource($user)
    {
        return [
            'uuid' => $user->uuid,
            'organization_name' => $user->organization_name,
            'address' => $user->address,
            'inn' => $user->inn,
            'kpp' => $user->kpp,
            'user_verify_status_id' => $user->user_verify_status_id,
            'created_at' => $user->created_at,
            'user_type_id' => $user->user_type_id,
        ];
    }
}
