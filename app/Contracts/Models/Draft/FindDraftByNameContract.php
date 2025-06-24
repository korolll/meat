<?php

namespace App\Contracts\Models\Draft;

use App\Models\Draft;
use App\Models\User;

interface FindDraftByNameContract
{
    /**
     * @param User $user
     * @param string $name
     * @return Draft
     */
    public function find(User $user, string $name): Draft;
}
