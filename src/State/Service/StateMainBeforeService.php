<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service;

use Duyler\ActionBus\Dto\ActionHandlerSubstitution;
use Duyler\ActionBus\Dto\ActionResultSubstitution;
use Duyler\ActionBus\Formatter\IdFormatter;
use Duyler\ActionBus\Service\ActionService;
use Duyler\ActionBus\Service\LogService;
use Duyler\ActionBus\State\Service\Trait\LogServiceTrait;
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
