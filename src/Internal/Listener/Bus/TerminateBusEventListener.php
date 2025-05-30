<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Internal\Event\BusCompletedEvent;
use Duyler\EventBus\Termination;

class TerminateBusEventListener
{
    public function __construct(
        private readonly Termination $termination,
        private readonly BusConfig $config,
    ) {}

    public function __invoke(BusCompletedEvent $event)
    {
        if ($this->config->autoreset) {
            $this->termination->run();
        }
    }
}
