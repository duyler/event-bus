<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Dto\Rollback as RollbackDto;
use Duyler\EventBus\Storage\ActionArgumentStorage;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Contract\RollbackActionInterface;

use function is_callable;

final class Rollback
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private ActionContainerStorage $containerStorage,
        private ActionArgumentStorage $actionArgumentStorage,
        private Log $log,
    ) {}

    public function run(): void
    {
        $successLog = $this->log->getSuccessLog();

        $completeActions = $this->completeActionStorage->getAllByArray($successLog);
        foreach ($completeActions as $completeAction) {
            if (null === $completeAction->action->rollback) {
                continue;
            }

            $actionContainer = $this->containerStorage->get($completeAction->action->id);

            $argument = $this->actionArgumentStorage->isExists($completeAction->action->id)
                ? $this->actionArgumentStorage->get($completeAction->action->id)
                : null;

            $rollbackDto = new RollbackDto(
                container: $actionContainer,
                action: $completeAction->action,
                argument: $argument,
                result: $completeAction->result,
            );

            if (is_callable($completeAction->action->rollback)) {
                ($completeAction->action->rollback)($rollbackDto);
                continue;
            }

            /** @var RollbackActionInterface $rollback */
            $rollback = $actionContainer->get($completeAction->action->rollback);
            $this->rollback($rollback, $rollbackDto);
        }
    }

    private function rollback(RollbackActionInterface $rollback, RollbackDto $rollbackService): void
    {
        $rollback->run($rollbackService);
    }
}
