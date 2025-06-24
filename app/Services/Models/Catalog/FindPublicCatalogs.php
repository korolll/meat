<?php

namespace App\Services\Models\Catalog;

use App\Contracts\Models\Catalog\FindPublicCatalogsContract;
use App\Models\Catalog;
use Illuminate\Support\Collection;

class FindPublicCatalogs implements FindPublicCatalogsContract
{
    /**
     * @return Collection&Catalog[]
     */
    public function find(): Collection
    {
        return Catalog::public()->get();
    }
}
