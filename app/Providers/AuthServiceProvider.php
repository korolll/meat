<?php

namespace App\Providers;

use App\Policies\NotificationPolicy;
use App\Services\Authentication\ClientAuthenticationManager;
use App\Services\Authentication\ClientAuthenticationManagerContract;
use App\Services\Framework\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // @see boot()
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        Auth::provider('eloquent', function ($app, $config) {
            return new EloquentUserProvider($app['hash'], $config['model']);
        });

        /** @var Gate $gate */
        $gate = app(Gate::class);
        $gate->policy(DatabaseNotification::class, NotificationPolicy::class);
        $gate->guessPolicyNamesUsing(function (string $class) {
            return [str_replace('App\\Models', 'App\\Policies', $class) . 'Policy'];
        });

        $this->registerPolicies();
    }

    /**
     * @return void
     */
    public function register()
    {
        // Управление кодами и токенами аутентификации клиентов
        $this->app->singleton(ClientAuthenticationManagerContract::class, ClientAuthenticationManager::class);
    }
}
