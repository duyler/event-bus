<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Bus;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;

class ResolveHeldTasksEventListener
{
    public function __construct(private Bus $bus) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $this->bus->resolveHeldTasks();
    }
}
