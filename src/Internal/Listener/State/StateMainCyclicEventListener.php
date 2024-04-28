<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Internal\Event\DoCyclicEvent;

class StateMainCyclicEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(DoCyclicEvent $event): void
    {
        $this->stateMain->cyclic();
    }
}
