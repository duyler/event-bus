<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Termination;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Internal\Event\BusCompletedEvent;

class TerminateBusEventListener
{
    public function __construct(
        private Termination $termination,
        private BusConfig $config,
    ) {}

    public function __invoke(BusCompletedEvent $event)
    {
        if ($this->config->autoreset) {
            $this->termination->run();
        }
    }
}
