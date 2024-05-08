<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Enum;

enum ResetMode
{
    case Full;
    case Selective;
}
