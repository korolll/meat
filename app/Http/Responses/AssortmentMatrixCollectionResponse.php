<?php

namespace App\Http\Responses;

use App\Http\Resources\AssortmentMatrixResource;
use App\Models\Assortment;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AssortmentMatrixCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = AssortmentMatrixResource::class;

    /**
     * @var string
     */
    protected $model = Assortment::class;

    /**
     * @var array
     */
    protected $attributes = [
        'uuid',
        'name',
        'short_name',
        'week_sales',
        'quantity',
        'catalog_uuid',
        'barcodes',
        'tags',
        'is_storable',
        'shelf_life',
        'manufacturer',
        'receipts_of_the_week',
        'offs_of_the_week',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'week_sales' => 'inner_query.week_sales',
        'quantity' => 'inner_query.quantity',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'tags',
        'barcodes',
    ];

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereTags(string $operator, $value)
    {
        return $this->query->whereExists(function (Builder $query) use ($operator, $value) {
            $query
                ->select(DB::raw(1))
                ->from('assortment_tag')
                ->join('tags', 'tags.uuid', '=', 'assortment_tag.tag_uuid')
                ->whereRaw('assortment_tag.assortment_uuid = assortments.uuid');

            self::whereWithAnyOperator($query, 'tags.name', $operator, $value);
        });
    }

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereBarcodes(string $operator, $value)
    {
        return $this->query->whereExists(function (Builder $query) use ($operator, $value) {
            $query
                ->select(\DB::raw(1))
                ->from('assortment_barcodes')
                ->whereRaw('assortment_barcodes.assortment_uuid = assortments.uuid');

            self::whereWithAnyOperator($query, 'assortment_barcodes.barcode', $operator, $value);
        });
    }
}
