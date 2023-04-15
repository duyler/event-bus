<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\StateBeforeService;
use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateServiceInterface;

interface StateBeforeHandlerInterface extends StateHandlerInterface
{
    public const TYPE_KEY = 'Before';

    public function handle(StateBeforeService $stateService): void;
}
