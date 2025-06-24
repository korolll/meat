<?php

namespace App\Services\Storaging\Catalog\Contracts;

use App\Models\Catalog;
use App\Models\User;

interface DefaultCatalogFinderContract
{
    /**
     * @param User $owner
     * @return Catalog
     */
    public function find(User $owner);
}
