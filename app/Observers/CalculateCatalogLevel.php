<?php

namespace App\Observers;

use App\Models\Catalog;

class CalculateCatalogLevel
{
    /**
     * Максимальный уровень вложенности каталогов
     */
    const MAXIMUM_CATALOG_NESTING_LEVEL = 10;

    /**
     * @param Catalog $catalog
     * @throws \Exception
     */
    public function saving(Catalog $catalog)
    {
        $catalog->level = 1;

        if ($catalog->catalog_uuid) {
            $catalog->level += $catalog->parent->level;
        }

        if ($catalog->level > static::MAXIMUM_CATALOG_NESTING_LEVEL) {
            throw new \Exception('Maximum catalog nesting level reached');
        }
    }
}
