<?php

namespace App\Services\Models\Draft;

use App\Contracts\Models\Draft\CreateDraftContract;
use App\Models\Draft;
use App\Models\User;

class CreateDraft implements CreateDraftContract
{
    /**
     * @param User $user
     * @param string $name
     * @param array $attributes
     * @return Draft
     */
    public function create(User $user, string $name, array $attributes): Draft
    {
        $where = [
            'user_uuid' => $user->uuid,
            'name' => $name,
        ];

        return Draft::updateOrCreate($where, compact('attributes'));
    }
}
