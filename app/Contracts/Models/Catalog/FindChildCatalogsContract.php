<?php

namespace App\Contracts\Models\Catalog;

use App\Models\Catalog;
use Illuminate\Support\Collection;

interface FindChildCatalogsContract
{
    /**
     * @param Catalog $parent
     * @return Collection&Catalog[]
     */
    public function find(Catalog $parent): Collection;
}
