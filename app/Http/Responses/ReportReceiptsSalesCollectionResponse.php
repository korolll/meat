<?php

namespace App\Http\Responses;

use App\Http\Resources\ReportReceiptsSalesProductResource;
use App\Models\Receipt;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class ReportReceiptsSalesCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ReportReceiptsSalesProductResource::class;

    /**
     * @var string
     */
    protected $model = Receipt::class;

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_name',
        'barcodes'
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'assortment_name',
        'barcodes'
    ];

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereAssortmentName(string $operator, $value)
    {
        return $this->query->with([
            'receiptLines' => function (HasMany $query) use ($value, $operator) {
                return $query->join('assortments', function (JoinClause $join) use ($value, $operator) {
                    $join->on('receipt_lines.assortment_uuid', '=', 'assortments.uuid');

                    self::whereWithAnyOperator($join, 'assortments.name', $operator, $value);
                });
            },
        ]);
    }

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereBarcodes(string $operator, $value)
    {
        return $this->query->with([
            'receiptLines' => function (HasMany $query) use ($value, $operator) {
                return $query->join('assortments', function (JoinClause $join) use ($value, $operator) {
                    $join->on('receipt_lines.assortment_uuid', '=', 'assortments.uuid')
                        ->whereExists(function (Builder $query) use ($operator, $value) {
                            $query
                                ->select(\DB::raw(1))
                                ->from('assortment_barcodes')
                                ->whereRaw('assortment_barcodes.assortment_uuid = assortments.uuid');

                            self::whereWithAnyOperator($query, 'assortment_barcodes.barcode', $operator, $value);
                        });
                });
            },
        ]);
    }
}
