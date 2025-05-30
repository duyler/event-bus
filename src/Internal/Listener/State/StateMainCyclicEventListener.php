<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\DoCyclicEvent;

class StateMainCyclicEventListener
{
    public function __construct(private readonly StateMainInterface $stateMain) {}

    public function __invoke(DoCyclicEvent $event): void
    {
        $this->stateMain->cyclic();
    }
}
