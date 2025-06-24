<?php

namespace App\Services\Models\Tag;

use App\Contracts\Models\Tag\FindOrCreateTagContract;
use App\Models\Tag;

class FindOrCreateTag implements FindOrCreateTagContract
{
    /**
     * @param string $name
     * @return Tag
     */
    public function findOrCreate(string $name): Tag
    {
        return Tag::query()
            ->firstOrCreate([
                'name' => $name
            ]);
    }
}
