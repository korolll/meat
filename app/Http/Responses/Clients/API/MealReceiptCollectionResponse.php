<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\MealReceiptResource;
use App\Models\MealReceipt;
use App\Services\Framework\Http\EloquentCollectionResponse;

class MealReceiptCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = MealReceiptResource::class;

    /**
     * @var string
     */
    protected $model = MealReceipt::class;

    /**
     * @var array
     */
    protected $attributes = [
        'uuid',
        'name',
        'section',
        'title',
        'description',
        'ingredients',
        'duration',
        'is_favorite',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'is_favorite',
    ];

    /**
     * @param string $operator
     * @param $value
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function whereIsFavorite(string $operator, $value)
    {
        if ($value) {
            return $this->query->whereNotNull('client_meal_receipt_favorites.meal_receipt_uuid');
        }

        return $this->query->whereNull('client_meal_receipt_favorites.meal_receipt_uuid');
    }
}
