<?php

namespace App\Http\Responses;

use App\Http\Resources\ReportReceiptsSummaryProductResource;
use App\Models\Receipt;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Support\Facades\DB;

class ReportReceiptsSummaryCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ReportReceiptsSummaryProductResource::class;

    /**
     * @var string
     */
    protected $model = Receipt::class;

    /**
     * @var array
     */
    protected $attributes = [
        'date',
    ];
}
