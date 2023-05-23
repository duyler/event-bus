<?php

declare(strict_types=1);

namespace Duyler\EventBus\Enum;

enum StateType
{
    case MainBeforeStart;
    case MainBeforeAction;
    case MainSuspendAction;
    case MainAfterAction;
    case MainFinal;
    case ActionBefore;
    case ActionThrowing;
    case ActionAfter;
}
