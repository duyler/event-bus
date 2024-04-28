<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Internal\Event\DoWhileEndEvent;

class StateMainEndEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(DoWhileEndEvent $event): void
    {
        $this->stateMain->end();
    }
}
