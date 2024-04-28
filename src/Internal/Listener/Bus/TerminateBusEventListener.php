<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Internal\Event\BusCompletedEvent;
use Duyler\ActionBus\Termination;

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
