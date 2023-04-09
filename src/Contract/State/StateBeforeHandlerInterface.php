<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\StateBeforeService;

interface StateBeforeHandlerInterface
{
    public const TYPE_KEY = 'Before';

    public function handle(StateBeforeService $stateService): void;
}
