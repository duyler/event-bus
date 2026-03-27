<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\ActorHandlerSubstitution;
use Duyler\EventBus\Build\ActorResultSubstitution;
use Duyler\EventBus\Bus\Task;
use Duyler\EventBus\Service\ActorService;
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
        private readonly ActorService $actorService,
        private readonly QueueService $queueService,
    ) {}

    public function substituteResult(ActorResultSubstitution $actorResultSubstitution): void
    {
        $this->actorService->addResultSubstitutions($actorResultSubstitution);
    }

    public function substituteHandler(ActorHandlerSubstitution $handlerSubstitution): void
    {
        $this->actorService->addHandlerSubstitution($handlerSubstitution);
    }

    public function getActorId(): string|UnitEnum
    {
        return $this->task->actor->getExternalId();
    }

    public function reject(): void
    {
        $this->task->reject();
    }
}
