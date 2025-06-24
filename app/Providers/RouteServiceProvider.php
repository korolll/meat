<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        parent::boot();

        //
    }

    /**
     * Define the routes for the application.
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        if (app()->environment() !== 'production') {
            $this->mapDevRoutes();
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware([
                'api',
                \Spatie\ResponseCache\Middlewares\CacheResponse::class,
            ])
            ->namespace($this->namespace . '\\API')
            ->group(base_path('routes/api.php'));

        Route::prefix('clients/api')
            ->as('clients.')
            ->middleware([
                'api',
                'last-visited',
                \Spatie\ResponseCache\Middlewares\CacheResponse::class,
            ])
            ->namespace($this->namespace . '\\Clients\\API')
            ->group(base_path('routes/api-clients.php'));

        Route::prefix('drivers/api')
            ->as('drivers.')
            ->middleware('api')
            ->namespace($this->namespace . '\\Drivers\\API')
            ->group(base_path('routes/api-drivers.php'));

        Route::prefix('integrations/cash-registers/api')
            ->as('integrations.cash-registers.')
            ->middleware('api')
            ->namespace($this->namespace . '\\Integrations\\CashRegisters\\API')
            ->group(base_path('routes/integrations/cash-registers/api.php'));

        Route::prefix('integrations/receipts/api')
            ->as('integrations.receipts.')
            ->middleware('api')
            ->namespace($this->namespace . '\\Integrations\\Receipts\\API')
            ->group(base_path('routes/integrations/receipts/api.php'));

        Route::prefix('integrations/frontol/api')
            ->as('integrations.frontol.')
            ->middleware([
                'api',
                'log-req-res:frontol-loyalty-system',
            ])
            ->namespace($this->namespace . '\\Integrations\\Frontol\\API')
            ->group(base_path('routes/integrations/frontol/api.php'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->as('web.')
            ->namespace($this->namespace. '\\Web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Роуты для разработки, возможно какие-то тестовые
     */
    protected function mapDevRoutes()
    {
        $basePath = base_path('routes/dev');

        if (app('files')->isDirectory($basePath)) {
            foreach (app('files')->files($basePath) as $file) {
                Route::namespace($this->namespace)->group($basePath . '/' . $file->getFilename());
            }
        }
    }
}
