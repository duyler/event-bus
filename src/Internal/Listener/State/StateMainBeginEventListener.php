<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\DoWhileBeginEvent;

class StateMainBeginEventListener
{
    public function __construct(private StateMainInterface $stateMain) {}

    public function __invoke(DoWhileBeginEvent $event): void
    {
        $this->stateMain->begin();
    }
}
