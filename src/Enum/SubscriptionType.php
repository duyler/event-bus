<?php

declare(strict_types=1);

namespace Duyler\EventBus\Enum;

enum SubscriptionType
{
    case None;
    case One;
    case Any;
    case All;
}
