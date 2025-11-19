<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case TRANSFER = 'transfer';
}
