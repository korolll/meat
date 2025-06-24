<?php

namespace App\Http\Responses;

use App\Http\Resources\StocktakingProductResource;
use App\Models\Product;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StocktakingProductCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = StocktakingProductResource::class;

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
        'assortment_catalog_uuid',
        'assortment_catalog_name',
        'assortment_barcode',
        'write_off_reason_id',
        'quantity_old',
        'quantity_new',
        'comment',
        'tags',
        'barcodes'
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_uuid' => 'assortment.uuid',
        'assortment_name' => 'assortment.name',
        'write_off_reason_id' => 'pivot_write_off_reason_id',
        'quantity_old' => 'pivot_quantity_old',
        'quantity_new' => 'pivot_quantity_new',
        'comment' => 'pivot_comment',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'tags',
        'barcodes',
        'assortment_catalog_uuid',
        'assortment_catalog_name',
    ];

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereTags(string $operator, $value)
    {
        if ($operator === 'in' && !is_array($value)) {
            $value = (array) $value;
        }

        return $this->query->whereExists(static function (Builder $query) use ($operator, $value) {
            $query
                ->select(DB::raw(1))
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
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereAssortmentCatalogName(string $operator, $value)
    {
        return $this->filterByAssortmentCatalog('name', $operator, $value);
    }

    /**
     * @param string $operator
     * @param $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereAssortmentCatalogUuid(string $operator, $value)
    {
        return $this->filterByAssortmentCatalog('uuid', $operator, $value);
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

    /**
     * @param string $colName
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function filterByAssortmentCatalog(string $colName, string $operator, $value)
    {
        return $this->query->whereExists(static function (Builder $query) use ($colName, $operator, $value) {
            $query
                ->select(\DB::raw(1))
                ->from('catalogs')
                ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid')
                ->whereRaw('catalogs.uuid = assortments.catalog_uuid');

            self::whereWithAnyOperator($query, "catalogs.{$colName}", $operator, $value);
        });
    }
}
