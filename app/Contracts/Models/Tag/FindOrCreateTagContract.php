<?php

namespace App\Contracts\Models\Tag;

use App\Models\Tag;

interface FindOrCreateTagContract
{
    /**
     * @param string $name
     * @return Tag
     */
    public function findOrCreate(string $name): Tag;
}
