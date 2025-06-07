<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\ActionHandlerSubstitution;
use Duyler\EventBus\Build\ActionResultSubstitution;
use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\QueueService;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\QueueServiceTrait;
use UnitEnum;

class StateMainBeforeService
{
    use LogServiceTrait;
    use QueueServiceTrait;

    public function __construct(
        private readonly Task $task,
        private readonly LogService $logService,
        private readonly ActionService $actionService,
        private readonly QueueService $queueService,
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
        return $this->task->action->externalId;
    }

    public function reject(): void
    {
        $this->task->reject();
    }
}
