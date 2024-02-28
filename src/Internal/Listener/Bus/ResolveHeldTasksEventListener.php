<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class ResolveHeldTasksEventListener
{
    public function __construct(private Bus $bus) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $this->bus->resolveHeldTasks();
    }
}
