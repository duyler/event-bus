<?php

declare(strict_types=1);

namespace Duyler\EventBus\Enum;

enum ResetMode
{
    case Soft;
    case Selective;
}
