<?php


namespace App\Services\Models\User;


use App\Models\User;
use App\Services\Models\Catalog\CatalogMapInterface;

interface ProductsInCatalogCacherInterface
{
    /**
     * @param \App\Models\User                                 $user
     * @param \App\Services\Models\Catalog\CatalogMapInterface $map
     */
    public function cache(User $user, CatalogMapInterface $map): void;
}
