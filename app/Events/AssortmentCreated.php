<?php

namespace App\Events;

use App\Models\Assortment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssortmentCreated
{
    use Dispatchable, SerializesModels;

    /**
     * @var Assortment
     */
    public $assortment;

    /**
     * AssortmentCreated constructor.
     *
     * @param Assortment $assortment
     */
    public function __construct(Assortment $assortment)
    {
        $this->assortment = $assortment;
    }
}
