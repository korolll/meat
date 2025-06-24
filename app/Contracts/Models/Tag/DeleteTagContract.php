<?php

namespace App\Contracts\Models\Tag;

use App\Models\Tag;

interface DeleteTagContract
{
    /**
     * @param Tag $tag
     * @return Tag
     */
    public function delete(Tag $tag): Tag;
}
