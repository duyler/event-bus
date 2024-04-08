<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Dto\ActionHandlerSubstitution;
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

    /** @param array<string, object> $substitutions */
    public function substituteResult(string $actionId, array $substitutions): void
    {
        $this->actionService->addResultSubstitutions($actionId, $substitutions);
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
