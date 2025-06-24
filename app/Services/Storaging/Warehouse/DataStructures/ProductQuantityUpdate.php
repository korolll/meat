<?php

namespace App\Services\Storaging\Warehouse\DataStructures;

use App\Services\Framework\HasStaticMakeMethod;

class ProductQuantityUpdate
{
    use HasStaticMakeMethod;

    /**
     * @var int
     */
    public $old;

    /**
     * @var int
     */
    public $delta;

    /**
     * @var int
     */
    public $new;

    /**
     * @param int $old
     * @param int $delta
     * @param int $new
     */
    public function __construct($old, $delta, $new)
    {
        $this->old = $old;
        $this->delta = $delta;
        $this->new = $new;
    }
}
