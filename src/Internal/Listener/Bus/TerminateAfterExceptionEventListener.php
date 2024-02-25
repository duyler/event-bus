<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\ThrowExceptionEvent;
use Duyler\EventBus\Termination;

class TerminateAfterExceptionEventListener
{
    public function __construct(private Termination $termination) {}

    public function __invoke(ThrowExceptionEvent $event)
    {
        $this->termination->run();
    }
}
