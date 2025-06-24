<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\StoreResourceCollection;
use App\Models\User;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Eloquent\Builder;

class StoreCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = StoreResourceCollection::class;

    /**
     * @var string
     */
    protected $model = User::class;

    /**
     * @var array
     */
    protected $attributes = [
        'uuid',
        'brand_name',
        'work_hours_from',
        'work_hours_till',
        'address_latitude',
        'address_longitude',
        'loyalty_card_type_uuid',
        'address',
        'is_favorite',
        'has_parking',
        'has_ready_meals',
        'has_atms',
        'delivery_price',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'loyalty_card_type_uuid',
        'is_favorite',
    ];

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereLoyaltyCardTypeUuid(string $operator, $value)
    {
        return $this->query->whereHas('loyaltyCardTypes', function (Builder $query) use ($value, $operator) {
            $column = 'loyalty_card_types.uuid';
            switch ($operator) {
                case 'in':
                    return $query->whereIn($column, (array)$value);
                case 'not in':
                    return $query->whereNotIn($column, (array)$value);
                default:
                    return $query->where($column, $operator, $value);
            }
        });
    }

    /**
     * @param string $operator
     * @param $value
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function whereIsFavorite(string $operator, $value)
    {
        if ($value) {
            return $this->query->whereNotNull('client_user_favorites.user_uuid');
        }

        return $this->query->whereNull('client_user_favorites.user_uuid');
    }
}
