<?php

namespace App\Services\Models\Draft;

use App\Contracts\Models\Draft\FindDraftByNameContract;
use App\Models\Draft;
use App\Models\User;

class FindDraftByName implements FindDraftByNameContract
{
    /**
     * @param User $user
     * @param string $name
     * @return Draft
     */
    public function find(User $user, string $name): Draft
    {
        $query = Draft::where([
            'user_uuid' => $user->uuid,
            'name' => $name,
        ]);

        return $query->firstOrFail();
    }
}
