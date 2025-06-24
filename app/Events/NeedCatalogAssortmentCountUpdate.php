<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NeedCatalogAssortmentCountUpdate
{
    use Dispatchable, SerializesModels;

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
