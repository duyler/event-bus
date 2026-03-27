<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Contract\RollbackActorInterface;
use Duyler\EventBus\Dto\Rollback as RollbackDto;
use Duyler\EventBus\Storage\ActorContainerStorage;
use Duyler\EventBus\Storage\TaskStorage;

use function is_callable;

final readonly class Rollback
{
    public function __construct(
        private ActorContainerStorage $containerStorage,
        private TaskStorage $taskStorage,
        private State $state,
    ) {}

    public function run(): void
    {
        $successLog = $this->state->getSuccessLog();

        foreach ($successLog as $actorId) {
            $tasks = $this->taskStorage->getAllByActorId($actorId);
            foreach ($tasks as $task) {

                $actorRollback = $task->actor->getRollback();

                if (null === $actorRollback) {
                    continue;
                }

                $actorContainer = $this->containerStorage->get($task->actor->getId());

                $rollbackDto = new RollbackDto(
                    container: $actorContainer,
                    actor: $task->actor,
                    argument: $task->getRunner()?->getArgument(),
                    result: $task->getResult(),
                );

                if (is_callable($actorRollback)) {
                    ($actorRollback)($rollbackDto);
                    continue;
                }

                /** @var RollbackActorInterface $rollback */
                $rollback = $actorContainer->get($actorRollback);
                $this->rollback($rollback, $rollbackDto);
            }
        }
    }

    private function rollback(RollbackActorInterface $rollback, RollbackDto $rollbackService): void
    {
        $rollback->run($rollbackService);
    }
}
