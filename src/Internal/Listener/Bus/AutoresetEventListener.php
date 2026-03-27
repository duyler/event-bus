<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Internal\Event\TaskQueueIsEmptyEvent;
use Duyler\EventBus\Termination;

class AutoresetEventListener
{
    public function __construct(
        private readonly Termination $termination,
        private readonly BusConfig $config,
    ) {}

    public function __invoke(TaskQueueIsEmptyEvent $event)
    {
        if ($this->config->autoreset && Mode::Loop === $this->config->mode) {
            $this->termination->run();
        }
    }
}
