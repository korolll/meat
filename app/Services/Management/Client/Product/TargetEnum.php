<?php

namespace App\Services\Management\Client\Product;

enum TargetEnum: int
{
    case RECEIPT = 0;
    case ORDER = 1;
    case API_ASSORTMENT = 2;
}
