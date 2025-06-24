<?php

namespace App\Services\Models\Tag;

use App\Contracts\Models\Tag\DeleteTagContract;
use App\Models\Tag;

class DeleteTag implements DeleteTagContract
{
    /**
     * @param Tag $tag
     * @return Tag
     * @throws \Throwable
     */
    public function delete(Tag $tag): Tag
    {
        $tag->delete();
        return $tag;
    }
}
