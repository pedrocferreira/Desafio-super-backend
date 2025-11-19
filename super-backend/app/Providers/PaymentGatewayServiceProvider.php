<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Strategies\SubAdquirenteAStrategy;
use App\Services\Payments\Strategies\SubAdquirenteBStrategy;
use Illuminate\Support\ServiceProvider;

class PaymentGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('gateway.subadquirente_a', SubAdquirenteAStrategy::class);
        $this->app->bind('gateway.subadquirente_b', SubAdquirenteBStrategy::class);

        $this->app->bind(PaymentGatewayInterface::class, SubAdquirenteAStrategy::class);
    }

    public function boot(): void
    {
        //
    }
}
