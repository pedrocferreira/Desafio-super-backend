<?php

declare(strict_types=1);

namespace App\Services\Subadquirentes;

use App\Enums\SubadquirenteType;
use App\Services\Subadquirentes\Contracts\SubadquirenteGatewayInterface;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class SubadquirenteGatewayManager
{
    public function __construct(private readonly Application $app)
    {
    }

    public function resolve(SubadquirenteType|string $type): SubadquirenteGatewayInterface
    {
        $name = $type instanceof SubadquirenteType ? $type->value : $type;
        $binding = sprintf('subadquirente.%s', $name);

        if (! $this->app->bound($binding)) {
            throw new InvalidArgumentException(sprintf('Subadquirente não suportada: %s', $name));
        }

        /** @var SubadquirenteGatewayInterface $gateway */
        $gateway = $this->app->make($binding);

        return $gateway;
    }
}

