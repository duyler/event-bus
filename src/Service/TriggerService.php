<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus\TriggerDispatcher;
use Duyler\EventBus\Dto\Trigger;

class TriggerService
{
    public function __construct(
        private TriggerDispatcher $dispatcher,
    ) {}

    public function dispatch(Trigger $trigger): void
    {
        $this->dispatcher->dispatch($trigger);
    }
}
