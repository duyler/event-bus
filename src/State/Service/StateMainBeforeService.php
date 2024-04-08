<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Dto\ActionHandlerSubstitution;
use Duyler\EventBus\Dto\ActionResultSubstitution;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;

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

    public function getActionId(): string
    {
        return $this->actionId;
    }
}
