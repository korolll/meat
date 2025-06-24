<?php

namespace App\Services\Storaging\Catalog;

use App\Exceptions\ClientExceptions\CatalogNotEmptyException;
use App\Models\Assortment;
use App\Models\Catalog;
use App\Services\Storaging\Catalog\Contracts\CatalogRemoverContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CatalogRemover implements CatalogRemoverContract
{
    /**
     * @var Catalog
     */
    protected $catalog;

    /**
     * @var Collection|Catalog[]
     */
    protected $containsCatalogs;

    /**
     * @var Collection|Assortment[]
     */
    protected $containsAssortments;

    /**
     * @param Catalog $catalog
     * @throws CatalogNotEmptyException
     */
    public function remove(Catalog $catalog)
    {
        $this->catalog = $catalog;

        $this->loadCatalogs();
        $this->loadAssortments();

        if ($this->containsAssortments->isNotEmpty()) {
            throw new CatalogNotEmptyException($this->catalog, $this->containsAssortments);
        }

        $this->deleteCatalogs();
    }

    /**
     * @return void
     */
    protected function loadCatalogs()
    {
        $this->containsCatalogs = Collection::make();

        $this->readCatalogs($this->catalog);
    }

    /**
     * @param Catalog $catalog
     */
    protected function readCatalogs(Catalog $catalog)
    {
        foreach ($catalog->child as $child) {
            $this->readCatalogs($child);
        }

        $this->containsCatalogs->push($catalog);
    }

    /**
     * @return void
     */
    protected function loadAssortments()
    {
        $this->containsAssortments = Collection::make();

        foreach ($this->containsCatalogs as $catalog) {
            $this->readAssortments($catalog);
        }
    }

    /**
     * @param Catalog $catalog
     */
    protected function readAssortments(Catalog $catalog)
    {
        if ($catalog->is_public) {
            $assortments = $catalog->assortments;
        } else {
            $assortments = $catalog->assortmentsThroughProducts;
        }

        $this->containsAssortments = $this->containsAssortments->merge($assortments);
    }

    /**
     * @return void
     */
    protected function deleteCatalogs()
    {
        DB::transaction(function () {
            foreach ($this->containsCatalogs as $catalog) {
                $catalog->delete();
            }
        });
    }
}
