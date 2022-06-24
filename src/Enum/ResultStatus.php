<?php

declare(strict_types=1);

namespace Konveyer\EventBus\Enum;

enum ResultStatus: string
{
    case POSITIVE = 'Positive';
    case NEGATIVE = 'Negative';
}
