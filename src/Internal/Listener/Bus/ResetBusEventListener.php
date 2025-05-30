<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Internal\Event\BusIsResetEvent;
use Duyler\EventBus\Termination;

class ResetBusEventListener
{
    public function __construct(
        private readonly Termination $termination,
    ) {}

    public function __invoke(BusIsResetEvent $event): void
    {
        $this->termination->run();
    }
}
