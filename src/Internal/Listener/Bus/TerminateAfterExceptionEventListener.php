<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Internal\Event\ThrowExceptionEvent;
use Duyler\ActionBus\Termination;

class TerminateAfterExceptionEventListener
{
    public function __construct(private Termination $termination) {}

    public function __invoke(ThrowExceptionEvent $event)
    {
        $this->termination->run();
    }
}
