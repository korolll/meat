<?php

namespace App\Http\Responses;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ClientCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ClientResource::class;

    /**
     * @var string
     */
    protected $model = Client::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'phone',
        'sex',
        'birth_date',
        'created_at',
        'updated_at',
        'consent_to_service_newsletter',
        'consent_to_receive_promotional_mailings',
        'last_visited_at',
        'app_version',
        'has_favorites',
        'has_goods_in_shopping_cart',
        'purchase_catalog_uuid',
        'purchase_date',
    ];

    /**
     * @var array
     */
    protected $nonSortable = [
        'has_favorites',
        'has_goods_in_shopping_cart',
        'purchase_catalog_uuid',
        'purchase_date',
    ];

    protected $preparedPurchasesRequest = [
        'categories' => null,
        'date' => null
    ];

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereHasFavorites(string $operator, $value)
    {
        $func = function (Builder $query) use ($operator, $value) {
            $query
                ->select(\DB::raw(1))
                ->from('assortment_client_favorites')
                ->whereRaw('assortment_client_favorites.client_uuid = clients.uuid');
        };

        if ($value) {
            return $this->query->whereExists($func);
        }

        return $this->query->whereNotExists($func);
    }

    /**
     * @param string $operator
     * @param $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereHasGoodsInShoppingCart(string $operator, $value)
    {
        // Common empty field is NULL or just {"data":[],"updated_at":"2024-10-25 21:24:03+0300"}
        $keysNumber = "LENGTH(shopping_cart_data->>'data')";
        if (!$value) {
            return $this->query->where(function (\Illuminate\Database\Eloquent\Builder $query) use ($keysNumber) {
                $query->whereRaw("$keysNumber <= 2"); // empty arr
                $query->orWhereNull('shopping_cart_data');
            });
        }

        $this->query->whereRaw("$keysNumber > 6");
        return $this->query->where(DB::raw("(shopping_cart_data->>'updated_at')::timestamptz"), '<', now()->subDay());
    }

    /**
     * @param string $operator
     * @param $value
     */
    public function wherePurchaseCatalogUuid(string $operator, $value)
    {
        if (!$value) {
            return;
        }

        $this->preparedPurchasesRequest['categories'] = [$operator, $value];
    }

    /**
     * @param string $operator
     * @param $value
     */
    public function wherePurchaseDate(string $operator, $value)
    {
        if (!$value) {
            return;
        }

        $this->preparedPurchasesRequest['date'] = [$operator, $value];
    }

    protected function prepareQuery()
    {
        parent::prepareQuery();
        if (!$this->preparedPurchasesRequest['categories'] || !$this->preparedPurchasesRequest['date']) {
            return;
        }

        $this->query->whereExists(function (Builder $query) {
            $query = $query
                ->select(\DB::raw(1))
                ->from('purchases_view')
                ->whereRaw('purchases_view.client_uuid = clients.uuid')
                ->join('products', 'purchases_view.product_uuid', '=', 'products.uuid')
                ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid');

            static::whereWithAnyOperator($query, 'assortments.catalog_uuid', ...$this->preparedPurchasesRequest['categories']);
            static::whereWithAnyOperator($query, 'purchases_view.bought_at', ...$this->preparedPurchasesRequest['date']);
        });
    }
}
