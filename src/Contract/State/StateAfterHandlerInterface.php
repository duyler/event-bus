<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\StateAfterService;
use Duyler\EventBus\State\StateHandlerInterface;

interface StateAfterHandlerInterface extends StateHandlerInterface
{
    public const TYPE_KEY = 'After';

    public function handle(StateAfterService $stateService): void;
}
