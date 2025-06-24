<?php

namespace App\Services\Models\Catalog;

use App\Contracts\Models\Catalog\FindChildCatalogsContract;
use App\Models\Catalog;
use Illuminate\Support\Collection;

class FindChildCatalogs implements FindChildCatalogsContract
{
    /**
     * @param Catalog $parent
     * @return Collection&Catalog[]
     */
    public function find(Catalog $parent): Collection
    {
        $catalogs = new Collection();

        foreach ($parent->child as $child) {
            $catalogs->push($child)->merge(
                $this->find($child)
            );
        }

        return $catalogs;
    }
}
