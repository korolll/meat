<?php

namespace App\Http\Responses;

use App\Http\Resources\PriceListProductResource;
use App\Models\Product;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Query\Builder;

class PriceListProductCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PriceListProductResource::class;

    /**
     * @var string
     */
    protected $model = Product::class;

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_uuid',
        'assortment_name',
        'barcodes',
        'catalog_uuid',
        'catalog_name',
        'price_old',
        'price_new',
        'price_recommended',
        'is_active',
        'tags',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_uuid' => 'assortment.uuid',
        'assortment_name' => 'assortment.name',
        'catalog_uuid' => 'catalog.uuid',
        'catalog_name' => 'catalog.name',
        'price_old' => 'pivot_price_old',
        'price_new' => 'pivot_price_new',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'tags',
        'barcodes'
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
                ->select(\DB::raw(1))
                ->from('assortment_tag')
                ->join('tags', 'tags.uuid', '=', 'assortment_tag.tag_uuid')
                ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid')
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
                ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid')
                ->whereRaw('assortment_barcodes.assortment_uuid = assortments.uuid');

            self::whereWithAnyOperator($query, 'assortment_barcodes.barcode', $operator, $value);
        });
    }
}
