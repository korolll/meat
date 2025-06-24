<?php

namespace App\Providers\Management;

use App\Services\Management\Promos\DiverseFood\CalculatedClientDiscountsByCurrentMonthReport;
use App\Services\Management\Promos\DiverseFood\CalculatedClientDiscountsByCurrentMonthReportInterface;
use App\Services\Management\Promos\DiverseFood\DiscountCalculator;
use App\Services\Management\Promos\DiverseFood\DiscountCalculatorInterface;
use App\Services\Management\Promos\DiverseFood\ClientStatisticReport\ReportBuilder as ClientStatisticReportBuilder;
use App\Services\Management\Promos\DiverseFood\SettingFinder;
use App\Services\Management\Promos\DiverseFood\SettingFinderInterface;
use App\Services\Management\Promos\FavoriteAssortment\FavoriteAssortmentActivator;
use App\Services\Management\Promos\FavoriteAssortment\FavoriteAssortmentActivatorInterface;
use App\Services\Management\Promos\FavoriteAssortment\Resolver\FavoriteAssortmentVariantResolver;
use App\Services\Management\Promos\FavoriteAssortment\Resolver\FavoriteAssortmentVariantResolverInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class PromoServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     *
     */
    public function register()
    {
        $this->app->bind(CalculatedClientDiscountsByCurrentMonthReportInterface::class, function ($app) {
            return new CalculatedClientDiscountsByCurrentMonthReport(new ClientStatisticReportBuilder(
                Date::now()->startOfMonth()->subMonth(),
                Date::now()->startOfMonth()->subMonth()->endOfMonth()
            ));
        });

        $this->app->singleton(FavoriteAssortmentActivatorInterface::class, FavoriteAssortmentActivator::class);
        $this->app->singleton(FavoriteAssortmentVariantResolverInterface::class, FavoriteAssortmentVariantResolver::class);
        $this->app->singleton(DiscountCalculatorInterface::class, DiscountCalculator::class);
        $this->app->singleton(SettingFinderInterface::class, SettingFinder::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            SettingFinderInterface::class,
            DiscountCalculatorInterface::class,
            CalculatedClientDiscountsByCurrentMonthReportInterface::class,
            FavoriteAssortmentActivatorInterface::class,
            FavoriteAssortmentVariantResolverInterface::class,
        ];
    }
}
