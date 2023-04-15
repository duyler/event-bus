<?php

declare(strict_types=1);

namespace Duyler\EventBus\Contract\State;

use Duyler\EventBus\State\StateHandlerInterface;
use Duyler\EventBus\State\StateServiceInterface;
use Duyler\EventBus\State\StateStartService;

interface StateStartHandlerInterface extends StateHandlerInterface
{
    public const TYPE_KEY = 'Start';

    public function handle(StateStartService $stateService): void;
}
