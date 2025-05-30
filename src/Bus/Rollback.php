<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Rollback as RollbackDto;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Storage\TaskStorage;

use function is_callable;

final readonly class Rollback
{
    public function __construct(
        private ActionContainerStorage $containerStorage,
        private TaskStorage $taskStorage,
        private State $state,
    ) {}

    public function run(): void
    {
        $successLog = $this->state->getSuccessLog();

        foreach ($successLog as $actionId) {
            $tasks = $this->taskStorage->getAllByActionId($actionId);
            foreach ($tasks as $task) {
                if (null === $task->action->rollback) {
                    continue;
                }

                $actionContainer = $this->containerStorage->get($task->action->id);

                $rollbackDto = new RollbackDto(
                    container: $actionContainer,
                    action: $task->action,
                    argument: $task->getRunner()?->getArgument(),
                    result: $task->getResult(),
                );

                if (is_callable($task->action->rollback)) {
                    ($task->action->rollback)($rollbackDto);
                    continue;
                }

                /** @var RollbackActionInterface $rollback */
                $rollback = $actionContainer->get($task->action->rollback);
                $this->rollback($rollback, $rollbackDto);
            }
        }
    }

    private function rollback(RollbackActionInterface $rollback, RollbackDto $rollbackService): void
    {
        $rollback->run($rollbackService);
    }
}
