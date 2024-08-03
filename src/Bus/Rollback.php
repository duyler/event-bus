<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Dto\Rollback as RollbackDto;
use Duyler\ActionBus\Storage\ActionArgumentStorage;
use Duyler\ActionBus\Storage\ActionContainerStorage;
use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Contract\RollbackActionInterface;

use function is_callable;

final class Rollback
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private ActionContainerStorage $containerStorage,
        private ActionArgumentStorage $actionArgumentStorage,
    ) {}

    public function run(array $slice = []): void
    {
        $completeActions = empty($slice)
            ? $this->completeActionStorage->getAll()
            : $this->completeActionStorage->getAllByArray($slice);

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

    public function rollbackSingleAction(Action $action): void
    {
        if (null === $action->rollback) {
            return;
        }

        $actionContainer = $this->containerStorage->get($action->id);

        $argument = $this->actionArgumentStorage->isExists($action->id)
            ? $this->actionArgumentStorage->get($action->id)
            : null;

        $rollbackDto = new RollbackDto(
            container: $actionContainer,
            action: $action,
            argument: $argument,
        );

        if (is_callable($action->rollback)) {
            ($action->rollback)($rollbackDto);
            return;
        }

        /** @var RollbackActionInterface $rollback */
        $rollback = $actionContainer->get($action->rollback);
        $this->rollback($rollback, $rollbackDto);
    }
}
