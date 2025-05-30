<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;

class StateMainEndEventListener
{
    public function __construct(private readonly StateMainInterface $stateMain) {}

    public function __invoke(DoWhileEndEvent $event): void
    {
        $this->stateMain->end();
    }
}
