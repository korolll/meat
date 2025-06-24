<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\AssortmentResourceCollection;
use App\Models\Assortment;
use App\Services\Database\Table\ChildCatalogRecursiveTable;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        'uuid',
        'catalog_uuid',
        'catalog_name',
        'assortment_brand_uuid',
        'assortment_brand_name',
        'name',
        'barcodes',
        'weight',
        'volume',
        'rating',
        'manufacturer',
        'tags',
        'is_favorite',
        'properties',
        'catalog_with_children_uuid',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'catalog_name' => 'catalog.name',
        'assortment_brand_uuid' => 'assortmentBrand.uuid',
        'assortment_brand_name' => 'assortmentBrand.name',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'tags',
        'is_favorite',
        'properties',
        'barcodes',
        'catalog_with_children_uuid',
    ];

    /**
     * @param string $operator
     * @param        $value
     *
     * @return \Illuminate\Database\Eloquent\Builder|void
     */
    public function whereCatalogWithChildrenUuid(string $operator, $value)
    {
        switch (Str::lower($operator)) {
            case '':
            case '=':
            case 'in':
                $value = Arr::wrap($value);
                break;
            default:
                return;
        }

        $class = new ChildCatalogRecursiveTable($value);
        $table = $class->table('c');
        $ids = $table->pluck('uuid');
        if ($ids->isNotEmpty()) {
            return $this->query->whereIn('assortments.catalog_uuid', $ids);
        }
    }

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereIsFavorite(string $operator, $value)
    {
        $hasJoin = false;
        $query = $this->query->getQuery();
        $joins = $query->joins;
        /** @var JoinClause $join */
        foreach ($joins as $join) {
            if ($join->table === 'assortment_client_favorites') {
                $hasJoin = true;
                break;
            }
        };

        if (! $hasJoin) {
            return $this->query; // ignore filter
        }

        if ($value) {
            return $this->query->whereNotNull('assortment_client_favorites.assortment_uuid');
        } else {
            return $this->query->whereNull('assortment_client_favorites.assortment_uuid');
        }
    }

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereTags(string $operator, $value)
    {
        return $this->query->whereExists(function (Builder $query) use ($operator, $value) {
            return $query
                ->select(DB::raw(1))
                ->from('assortment_tag')
                ->join('tags', 'tags.uuid', '=', 'assortment_tag.tag_uuid')
                ->whereIn('tags.name', (array) $value)
                ->whereRaw('assortment_tag.assortment_uuid = assortments.uuid');
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
}
