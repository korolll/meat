<?php

namespace App\Services\Management\PriceList;

use App\Models\PriceList;
use App\Models\PriceListStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProductManager implements ProductManagerContract
{
    /**
     * @param PriceList $priceList
     * @return int
     */
    protected function synchronizeForStore(PriceList $priceList): int
    {
        // Удаляем те, которых нет в матрице товаров
        $deleted = \DB::table('price_list_product')
            ->where('price_list_product.price_list_uuid', $priceList->uuid)
            ->whereNotExists(function (Builder $query) use ($priceList) {
                $query->select(DB::raw(1))
                    ->from('assortment_matrices')
                    ->join('products', 'products.assortment_uuid', '=', 'assortment_matrices.assortment_uuid')
                    ->whereRaw('price_list_product.product_uuid = products.uuid');
            })
            ->delete();

        // Добавляем все те, что есть в матрице товаров
        $query = 'INSERT INTO price_list_product 
                  SELECT ?, products.uuid, current_price_list_product.price_new, current_price_list_product.price_new 
                  FROM assortment_matrices
                  JOIN products ON products.assortment_uuid = assortment_matrices.assortment_uuid
                  
                  LEFT JOIN price_lists current_price_list ON 
                    current_price_list.user_uuid = products.user_uuid 
                    AND current_price_list.price_list_status_id = \'' . PriceListStatus::CURRENT . '\' 
                    AND ' . $this->makeCustomerCondition($priceList) . '
                    
                  LEFT JOIN price_list_product current_price_list_product ON 
                    current_price_list.uuid = current_price_list_product.price_list_uuid AND 
                    current_price_list_product.product_uuid = products.uuid
                  WHERE products.user_uuid = ? ON CONFLICT DO NOTHING';
        return $deleted + DB::affectingStatement($query, [$priceList->uuid, $priceList->user_uuid]);
    }

    /**
     * @param PriceList $priceList
     * @return int
     */
    protected function synchronizeDefault(PriceList $priceList): int
    {
        $query = 'INSERT INTO price_list_product 
                  SELECT ?, products.uuid, current_price_list_product.price_new, current_price_list_product.price_new
                  FROM products 
                  LEFT JOIN price_lists current_price_list ON 
                    current_price_list.user_uuid = products.user_uuid AND 
                    current_price_list.price_list_status_id = \'' . PriceListStatus::CURRENT . '\' 
                    AND ' . $this->makeCustomerCondition($priceList) . '
                    
                  LEFT JOIN price_list_product current_price_list_product ON 
                    current_price_list.uuid = current_price_list_product.price_list_uuid AND 
                    current_price_list_product.product_uuid = products.uuid
                  WHERE products.user_uuid = ? ON CONFLICT DO NOTHING';
        return DB::affectingStatement($query, [$priceList->uuid, $priceList->user_uuid]);
    }

    /**
     * @param PriceList $priceList
     * @return int
     */
    public function synchronize(PriceList $priceList)
    {
        if ($priceList->user->is_store) {
            return $this->synchronizeForStore($priceList);
        }

        return $this->synchronizeDefault($priceList);
    }

    /**
     * @param PriceList $priceList
     * @return string
     */
    protected function makeCustomerCondition(PriceList $priceList): string
    {
        $conditionCustomer = 'current_price_list.customer_user_uuid IS NULL';
        if ($priceList->customer_user_uuid) {
            $conditionCustomer = 'current_price_list.customer_user_uuid = \'' . $priceList->customer_user_uuid . '\'';
        }
        return $conditionCustomer;
    }
}
