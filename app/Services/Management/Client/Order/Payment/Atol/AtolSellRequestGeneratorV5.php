<?php

namespace App\Services\Management\Client\Order\Payment\Atol;

use App\Models\Assortment;
use App\Models\AssortmentUnit;

class AtolSellRequestGeneratorV5 extends AbstractAtolSellRequestGenerator
{
    const VERSION = 5;

    /**
     * @see https://atol.online/upload/iblock/b3f/t09j33z47j6rv3v1h81vyv7dyz5h4q9x/API_atol_online_v5.pdf
     */
    const PAYMENT_OBJECT_COMMODITY = 1;
    const PAYMENT_OBJECT_SERVICE = 4;
    const MEASURE_TYPE_PIECE = 0;
    const MEASURE_TYPE_KILOS = 11;

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
