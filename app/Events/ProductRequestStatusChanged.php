<?php

namespace App\Events;

use App\Models\ProductRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ProductRequestStatusChanged
 *
 * @package App\Events
 */
class ProductRequestStatusChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var ProductRequest
     */
    public $productRequest;
    /**
     * @var bool
     */
    public $isExportTo1C;
    /**
     * @var bool
     */
    public $isSendMail;

    /**
     * @param ProductRequest $productRequest
     * @param bool $isSendMail
     * @param bool $isExportTo1C
     */
    public function __construct(ProductRequest $productRequest, bool $isExportTo1C = false, bool $isSendMail = false)
    {
        $this->productRequest = $productRequest;
        $this->isSendMail = $isSendMail;
        $this->isExportTo1C = $isExportTo1C;
    }
}
