<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\StateAfterService;

interface StateAfterHandlerInterface
{
    public const TYPE_KEY = 'After';

    public function handle(StateAfterService $stateService): void;
}
