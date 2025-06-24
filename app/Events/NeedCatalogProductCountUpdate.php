<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class NeedCatalogProductCountUpdate
{
    use Dispatchable;

    /**
     * @var string
     */
    public $catalogUuid;

    /**
     * @param string $catalogUuid
     */
    public function __construct(string $catalogUuid)
    {
        $this->catalogUuid = $catalogUuid;
    }
}
