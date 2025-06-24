<?php

namespace App\Exceptions\ClientExceptions;

use App\Exceptions\ClientException;
use App\Models\Assortment;
use App\Models\Catalog;
use Illuminate\Support\Collection;

class CatalogNotEmptyException extends ClientException
{
    /**
     * @var Catalog
     */
    protected $catalog;

    /**
     * @var Collection|Assortment[]
     */
    protected $assortments;

    /**
     * @param Catalog $catalog
     * @param Collection|Assortment[] $assortments
     */
    public function __construct($catalog, Collection $assortments)
    {
        parent::__construct("Catalog {$catalog->uuid} is not empty");

        $this->catalog = $catalog;
        $this->assortments = $assortments;
    }

    /**
     * @return int
     */
    public function getExceptionCode(): int
    {
        return 1005;
    }

    /**
     * @return Catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * @return Collection
     */
    public function getAssortments()
    {
        return $this->assortments;
    }
}
