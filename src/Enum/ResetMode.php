<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Enum;

enum ResetMode
{
    case Soft;
    case Selective;
}
