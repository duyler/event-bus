<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Event;

use Duyler\ActionBus\Bus\Task;

readonly class TaskSuspendedEvent
{
    public function __construct(public Task $task) {}
}
