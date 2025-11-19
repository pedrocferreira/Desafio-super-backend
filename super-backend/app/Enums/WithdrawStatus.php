<?php

declare(strict_types=1);

namespace App\Enums;

enum WithdrawStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SUCCESS = 'success';
    case DONE = 'done';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}

