<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Storage\ActionArgumentStorage;
use Duyler\ActionBus\Storage\ActionContainerStorage;
use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Contract\RollbackActionInterface;
use Duyler\ActionBus\Dto\Result;

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

            $argument = $this->actionArgumentStorage->isExists($completeAction->action->id)
                ? $this->actionArgumentStorage->get($completeAction->action->id)
                : null;

            if (is_callable($completeAction->action->rollback)) {
                ($completeAction->action->rollback)($completeAction->result, $argument);
                continue;
            }

            $actionContainer = $this->containerStorage->get($completeAction->action->id);

            /** @var RollbackActionInterface $rollback */
            $rollback = $actionContainer->get($completeAction->action->rollback);
            $this->rollback($rollback, $completeAction->result, $argument);
        }
    }

    private function rollback(RollbackActionInterface $rollback, Result|null $result, object|null $argument): void
    {
        $rollback->run($result, $argument);
    }

    public function rollbackSingleAction(Action $action): void
    {
        if (null === $action->rollback) {
            return;
        }

        $argument = $this->actionArgumentStorage->isExists($action->id)
            ? $this->actionArgumentStorage->get($action->id)
            : null;

        if (is_callable($action->rollback)) {
            ($action->rollback)(null, $argument);
            return;
        }

        $actionContainer = $this->containerStorage->get($action->id);

        /** @var RollbackActionInterface $rollback */
        $rollback = $actionContainer->get($action->rollback);
        $this->rollback($rollback, null, $argument);
    }
}
