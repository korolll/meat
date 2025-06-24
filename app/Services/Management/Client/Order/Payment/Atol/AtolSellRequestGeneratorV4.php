<?php

namespace App\Services\Management\Client\Order\Payment\Atol;

use App\Models\Assortment;
use App\Models\AssortmentUnit;

class AtolSellRequestGeneratorV4 extends AbstractAtolSellRequestGenerator
{
    /**
     * @see https://atol.online/upload/iblock/dff/4yjidqijkha10vmw9ee1jjqzgr05q8jy/API_atol_online_v4.pdf
     */
    const PAYMENT_OBJECT_COMMODITY = 'commodity';
    const PAYMENT_OBJECT_SERVICE = 'service';
    const MEASURE_TYPE_PIECE = 'шт.';
    const MEASURE_TYPE_KILOS = 'кг.';

    protected function getItemMeasure(Assortment $assortment)
    {
        return $assortment->assortment_unit_id === AssortmentUnit::ID_KILOGRAM ? static::MEASURE_TYPE_KILOS : static::MEASURE_TYPE_PIECE;
    }

    protected function getItemPaymentObjectMethod()
    {
        return static::PAYMENT_OBJECT_COMMODITY;
    }

    protected function getDeliveryItemMeasure()
    {
        return static::MEASURE_TYPE_PIECE;
    }

    protected function getDeliveryItemPaymentObjectMethod()
    {
        return static::PAYMENT_OBJECT_SERVICE;
    }
}
