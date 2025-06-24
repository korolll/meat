<?php

namespace App\Http\Responses;

use App\Http\Resources\ProductResourceCollection;
use App\Models\Product;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProductCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ProductResourceCollection::class;

    /**
     * @var string
     */
    protected $model = Product::class;

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_name',
        'assortment_uuid',
        'assortment_verify_status_id',
        'catalog_name',
        'catalog_uuid',
        'delivery_weekdays',
        'created_at',
        'assortment_tags',
        'barcodes',
        'assortment_catalog_uuid',
        'assortment_catalog_name',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_name' => 'assortment.name',
        'assortment_uuid' => 'assortment.uuid',
        'assortment_verify_status_id' => 'assortment.assortment_verify_status_id',
        'catalog_name' => 'catalog.name',
        'catalog_uuid' => 'catalog.uuid',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'assortment_tags',
        'barcodes',
        'assortment_catalog_uuid',
        'assortment_catalog_name',
    ];

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereAssortmentTags(string $operator, $value)
    {
        return $this->query->whereExists(function (Builder $query) use ($operator, $value) {
            $query
                ->select(DB::raw(1))
                ->from('assortment_tag')
                ->join('tags', 'tags.uuid', '=', 'assortment_tag.tag_uuid')
                ->whereRaw('assortment_tag.assortment_uuid = products.assortment_uuid');

            self::whereWithAnyOperator($query, 'tags.name', $operator, $value);
        });
    }

    /**
     * @param string $operator
     * @param int $value
     * @return mixed
     */
    protected function whereDeliveryWeekdays(string $operator, int $value)
    {
        if ($operator !== '=' || $value < 0 || $value > 6) {
            return $this->query;
        }

        return $this->query->whereJsonContains('products.delivery_weekdays', $value);
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
