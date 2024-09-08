<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Event;

use Duyler\EventBus\Bus\Task;

readonly class TaskAfterRunEvent
{
    public function __construct(public Task $task) {}
}
