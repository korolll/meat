<?php

namespace App\Structures\Models\Assortment;

use Spatie\DataTransferObject\DataTransferObject;

class SavingAssortmentStructure extends DataTransferObject
{
    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var array
     */
    public $images = [];

    /**
     * @var array
     */
    public $files = [];

    /**
     * @var array
     */
    public $properties = [];

    /**
     * @var null|array
     */
    public $tags = null;

    /**
     * @var array
     */
    public $barcodes = [];

    /**
     * @var bool
     */
    public $forceSyncFiles = false;
}
