<?php

namespace App\Providers;

use App\Services\Subadquirentes\Contracts\SubadquirenteGatewayInterface;
use App\Services\Subadquirentes\Gateways\SubadqAGateway;
use App\Services\Subadquirentes\Gateways\SubadqBGateway;
use Illuminate\Support\ServiceProvider;

class SubadquirenteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('subadquirente.subadq_a', fn () => new SubadqAGateway());
        $this->app->bind('subadquirente.subadq_b', fn () => new SubadqBGateway());

        $this->app->bind(SubadquirenteGatewayInterface::class, fn ($app) => $app->make('subadquirente.subadq_a'));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
