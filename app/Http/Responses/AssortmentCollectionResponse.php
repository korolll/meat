<?php

namespace App\Http\Responses;

use App\Http\Resources\AssortmentResourceCollection;
use App\Models\Assortment;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class AssortmentCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = AssortmentResourceCollection::class;

    /**
     * @var string
     */
    protected $model = Assortment::class;

    /**
     * @var array
     */
    protected $attributes = [
        'catalog_name',
        'catalog_uuid',
        'name',
        'assortment_verify_status_id',
        'assortment_brand_uuid',
        'assortment_brand_name',
        'created_at',
        'tags',
        'barcodes',
        'is_storable',
        'shelf_life',
        'article',
        'properties',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'catalog_name' => 'catalog.name',
        'catalog_uuid' => 'catalog.uuid',
        'assortment_brand_uuid' => 'assortmentBrand.uuid',
        'assortment_brand_name' => 'assortmentBrand.name',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'tags',
        'barcodes',
        'properties',
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

            return self::whereWithAnyOperator($query, 'tags.name', $operator, $value);
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

            return self::whereWithAnyOperator($query, 'assortment_barcodes.barcode', $operator, $value);
        });
    }

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereProperties(string $operator, $value)
    {
        return $this->query->whereExists(function (Builder $query) use ($operator, $value) {
            $query
                ->select(DB::raw(1))
                ->from('assortment_assortment_property')
                ->join('assortment_properties', 'assortment_properties.uuid', '=', 'assortment_assortment_property.assortment_property_uuid')
                ->whereRaw('assortment_assortment_property.assortment_uuid = assortments.uuid');

            $value = (array)$value;
            $propertyName = Arr::get($value, 0);
            $added = false;
            if ($propertyName) {
                self::whereWithAnyOperator($query, 'assortment_properties.name', $operator, $propertyName);
                $added = true;
            }

            $propertyValue = Arr::get($value, 1);
            if ($propertyValue) {
                self::whereWithAnyOperator($query, 'assortment_assortment_property.value', $operator, $propertyValue);
                $added = true;
            }

            if (! $added) {
                throw new BadRequestHttpException('Bad provided `properties` filter');
            }

            return $query;
        });
    }
}
