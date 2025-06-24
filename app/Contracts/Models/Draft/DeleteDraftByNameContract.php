<?php

namespace App\Contracts\Models\Draft;

use App\Models\User;

interface DeleteDraftByNameContract
{
    /**
     * @param User $user
     * @param string $name
     * @return bool
     */
    public function delete(User $user, string $name): bool;
}
