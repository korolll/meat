<?php

namespace App\Contracts\Models\Draft;

use App\Models\Draft;
use App\Models\User;

interface CreateDraftContract
{
    /**
     * @param User $user
     * @param string $name
     * @param array $attributes
     * @return Draft
     */
    public function create(User $user, string $name, array $attributes): Draft;
}
