<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

use Konveyer\EventBus\DTO\Subscribe;

class ActionIdBuilder
{
    public static function byAction(Action $action): string
    {
        return $action->service . '.' . $action->name;
    }

    public static function byTask(Task $task): string
    {
        return $task->action->service . '.' . $task->action->name . '.' . $task->result->status->value;
    }

    public static function bySubscribe(Subscribe $subscribe): string
    {
        return $subscribe->subject . '.' . $subscribe->status->value;
    }
}
