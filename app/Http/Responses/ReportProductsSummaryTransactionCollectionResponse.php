<?php

namespace App\Http\Responses;

use App\Http\Resources\ReportProductsSummaryTransactionResource;
use App\Models\WarehouseTransaction;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ReportProductsSummaryTransactionCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ReportProductsSummaryTransactionResource::class;

    /**
     * @var string
     */
    protected $model = WarehouseTransaction::class;

    /**
     * @var array
     */
    protected $attributes = [
        'uuid',
        'quantity_delta',
        'reference_type',
        'created_at',
    ];

    /**
     * В поле created_at возможны одинаковые значения (1 момент времени)
     *
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orderByCreatedAt($direction)
    {
        return $this->query->orderBy('uuid', $direction);
    }
}
