<?php

namespace App\Events;

use App\Models\PriceList;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriceListReadyForExportAtol
{
    use Dispatchable, SerializesModels;

    /**
     * @var PriceList
     */
    public $priceList;

    /**
     * @param PriceList $priceList
     */
    public function __construct(PriceList $priceList)
    {
        $this->priceList = $priceList;
    }
}
