<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\CatalogResource;
use App\Models\Catalog;
use App\Services\Framework\Http\EloquentCollectionResponse;

class CatalogCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = CatalogResource::class;

    /**
     * @var string
     */
    protected $model = Catalog::class;

    /**
     * @var array
     */
    protected $attributes = [
        'catalog_uuid',
        'catalog_name',
        'name',
        'level',
        'assortments_count',
        'assortments_count_in_store',
        'sort_number',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'catalog_name' => 'parent.name',
        'catalog_uuid' => 'parent.uuid',
        'assortments_count_in_store' => 'user_catalog_product_counts.product_count',
    ];

    /**
     * @param string $operator
     * @param        $value
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|void
     * @throws \App\Exceptions\TealsyException
     */
    public function whereAssortmentsCountInStore(string $operator, $value)
    {
        if (! $this->baseQueryJoinExist('user_catalog_product_counts')) {
            return;
        }

        $column = $this->getQualifiedColumn('assortments_count_in_store');
        return $this::whereWithAnyOperator($this->query, $column, $operator, $value);
    }
}
