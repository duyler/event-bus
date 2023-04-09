<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\StateFinalService;

interface StateFinalHandlerInterface
{
    public const TYPE_KEY = 'Final';

    public function handle(StateFinalService $stateService): void;
}
