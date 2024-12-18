<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskUnresolvedEvent;

readonly class StateMainUnresolvedEventListener
{
    public function __construct(
        private StateMainInterface $stateMain,
    ) {}

    public function __invoke(TaskUnresolvedEvent $event): void
    {
        $this->stateMain->unresolved($event->task);
    }
}
