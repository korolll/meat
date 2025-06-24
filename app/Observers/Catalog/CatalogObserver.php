<?php

namespace App\Observers\Catalog;

use App\Events\PublicCatalogsReadyForExport1C;
use App\Models\Catalog;


class CatalogObserver
{
    /**
     * @param Catalog $catalog
     * @throws \Exception
     */
    public function saved(Catalog $catalog)
    {
        $watchingFields = [
            'uuid',
            'catalog_uuid',
            'name',
            'level',
            'created_at',
            'sort_number',
        ];
        if ($catalog->is_public && $catalog->isDirty($watchingFields)) {
            PublicCatalogsReadyForExport1C::dispatch();
        }
    }

    /**
     * @param Catalog $catalog
     */
    public function deleted(Catalog $catalog)
    {
        if ($catalog->is_public) {
            PublicCatalogsReadyForExport1C::dispatch();
        }
    }

}
