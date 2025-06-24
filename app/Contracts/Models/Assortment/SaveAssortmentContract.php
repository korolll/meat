<?php

namespace App\Contracts\Models\Assortment;

use App\Models\Assortment;
use App\Structures\Models\Assortment\SavingAssortmentStructure;

interface SaveAssortmentContract
{
    /**
     * @param Assortment $assortment
     * @param SavingAssortmentStructure $data
     * @return Assortment
     * @throws \Throwable
     */
    public function save(Assortment $assortment, SavingAssortmentStructure $data): Assortment;
}
