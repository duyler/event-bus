<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\ActionHandlerSubstitution;
use Duyler\EventBus\Build\ActionResultSubstitution;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use UnitEnum;

class StateMainBeforeService
{
    use LogServiceTrait;

    public function __construct(
        private readonly string $actionId,
        private readonly LogService $logService,
        private readonly ActionService $actionService,
    ) {}

    public function substituteResult(ActionResultSubstitution $actionResultSubstitution): void
    {
        $this->actionService->addResultSubstitutions($actionResultSubstitution);
    }

    public function substituteHandler(ActionHandlerSubstitution $handlerSubstitution): void
    {
        $this->actionService->addHandlerSubstitution($handlerSubstitution);
    }

    public function getActionId(): string|UnitEnum
    {
        return IdFormatter::reverse($this->actionId);
    }
}
