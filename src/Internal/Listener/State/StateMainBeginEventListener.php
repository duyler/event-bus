<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Contract\StateMainInterface;
use Duyler\ActionBus\Internal\Event\DoWhileBeginEvent;

class StateMainBeginEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(DoWhileBeginEvent $event): void
    {
        $this->stateMain->begin();
    }
}
