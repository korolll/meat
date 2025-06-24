<?php

namespace App\Events;

use App\Models\Assortment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssortmentUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * @var Assortment
     */
    public $assortment;

    /**
     * @param Assortment $assortment
     */
    public function __construct(Assortment $assortment)
    {
        $this->assortment = $assortment;
    }
}
