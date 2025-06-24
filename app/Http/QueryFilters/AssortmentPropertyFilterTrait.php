<?php

namespace App\Http\QueryFilters;


use App\Http\Responses\AssortmentCollectionResponse;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

/**
 * Trait AssortmentPropertyFilterTrait
 *
 * @package App\Http\QueryFilters
 */
trait AssortmentPropertyFilterTrait
{
    /**
     * @param Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $withJoinProducts
     */
    protected function indexAssortmentPropertyFilter(Request $request, $query, $withJoinProducts = false)
    {
        if ($request->exists('*RequestFilters')) {
            $params = $request->get('*RequestFilters');
            if (array_key_exists('assortment_properties', $params)) {
                $params = $params['assortment_properties'];
                $this->nestedWhere($query->getQuery(), $params, $withJoinProducts);
            }
        }
    }

    /**
     * @param $queryWhereNested Builder
     * @param $params array
     * @param $withJoinProducts
     * @param string $operatorWhere
     */
    private function nestedWhere($queryWhereNested, $params, $withJoinProducts, $operatorWhere = 'AND')
    {
        if (!$this->createNestedWhere($queryWhereNested, $params, $withJoinProducts, $operatorWhere)) {
            // Set nested where params
            foreach ($params as $param) {
                $isBreak = false;
                if (!is_array($param)) {
                    $param = $params;
                    $isBreak = true;
                }
                if (!$this->createNestedWhere($queryWhereNested, $param, $withJoinProducts, $operatorWhere)) {
                    // Set nested where params
                    $queryWhereNested->whereExists(static function ($query) use ($param, $withJoinProducts) {
                        /**
                         * @var $query Builder
                         */
                        $assortmentPropertyUuid = $param['uuid'];
                        $operator = $param['operator'];
                        $value = $param['value'];
                        if ($operator === 'between') {
                            $value = explode('|||', $value);
                        }

                        $query->select(DB::raw(1))
                            ->from('assortment_assortment_property')
                            ->whereRaw('assortment_assortment_property.assortment_uuid = assortments.uuid')
                            ->where('assortment_assortment_property.assortment_property_uuid', '=', $assortmentPropertyUuid);
                        if ($withJoinProducts) {
                            $query->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid');
                        }
                        AssortmentCollectionResponse::whereWithAnyOperator($query, 'assortment_assortment_property.value', $operator, $value);
                    }, $operatorWhere);
                }
                if ($isBreak) {
                    break;
                }
            }
        }
    }

    /**
     * @param $queryWhereNested Builder
     * @param $params array
     * @param $withJoinProducts
     * @param $operatorWhere string
     *
     * @return bool
     */
    private function createNestedWhere($queryWhereNested, $params, $withJoinProducts, $operatorWhere)
    {
        if (is_array($params) && (array_key_exists('OR', $params) || array_key_exists('AND', $params))) {
            // Create nested where
            $queryWhereNestedNew = $queryWhereNested->forNestedWhere();
            $this->nestedWhere($queryWhereNestedNew, current($params), $withJoinProducts, key($params));
            $queryWhereNested->addNestedWhereQuery($queryWhereNestedNew, $operatorWhere);

            return true;
        }

        return false;
    }
}
