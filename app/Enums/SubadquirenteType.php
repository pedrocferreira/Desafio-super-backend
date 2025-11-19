<?php

declare(strict_types=1);

namespace App\Enums;

enum SubadquirenteType: string
{
    case SUBADQ_A = 'subadq_a';
    case SUBADQ_B = 'subadq_b';

    public static function fromGatewayName(string $gateway): self
    {
        return match ($gateway) {
            'subadquirente_a', 'subadq_a' => self::SUBADQ_A,
            'subadquirente_b', 'subadq_b' => self::SUBADQ_B,
            default => throw new \InvalidArgumentException("Subadquirente inválido: {$gateway}"),
        };
    }
}

