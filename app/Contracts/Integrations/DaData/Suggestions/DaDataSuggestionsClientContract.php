<?php

namespace App\Contracts\Integrations\DaData\Suggestions;

use Illuminate\Support\Collection;

interface DaDataSuggestionsClientContract
{
    /**
     * @param string $query
     * @return Collection
     */
    public function banks(string $query): Collection;

    /**
     * @param string $query
     * @return Collection
     */
    public function organizations(string $query): Collection;
}
