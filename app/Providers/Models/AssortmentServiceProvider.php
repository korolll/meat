<?php

namespace App\Providers\Models;

use App\Contracts\Models\Assortment\Property\CastDataTypeFactoryContract;
use App\Contracts\Models\Assortment\Property\FindUniqueValuesContract;
use App\Contracts\Models\Assortment\SaveAssortmentContract;
use App\Services\Models\Assortment\BannedAssortmentChecker;
use App\Services\Models\Assortment\BannedAssortmentCheckerInterface;
use App\Services\Models\Assortment\Discount\AssortmentDiscountApplier;
use App\Services\Models\Assortment\Discount\AssortmentDiscountApplierInterface;
use App\Services\Models\Assortment\Property\CastDataTypeFactory;
use App\Services\Models\Assortment\Property\FindUniqueValues;
use App\Services\Models\Assortment\SaveAssortment;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AssortmentServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        BannedAssortmentCheckerInterface::class => BannedAssortmentChecker::class,
        SaveAssortmentContract::class => SaveAssortment::class,
        CastDataTypeFactoryContract::class => CastDataTypeFactory::class,
        FindUniqueValuesContract::class => FindUniqueValues::class,
        AssortmentDiscountApplierInterface::class => AssortmentDiscountApplier::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
