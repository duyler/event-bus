<?php

namespace Duyler\EventBus\Enum;

enum TaskStatus
{
    case Primary;
    case Retry;
}
