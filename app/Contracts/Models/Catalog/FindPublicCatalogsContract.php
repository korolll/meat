<?php

namespace App\Contracts\Models\Catalog;

use App\Models\Catalog;
use Illuminate\Support\Collection;

interface FindPublicCatalogsContract
{
    /**
     * @return Collection&Catalog[]
     */
    public function find(): Collection;
}
