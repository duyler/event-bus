<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Bus;

use Duyler\ActionBus\Collection\ActionArgumentCollection;
use Duyler\ActionBus\Collection\ActionContainerCollection;
use Duyler\ActionBus\Collection\CompleteActionCollection;
use Duyler\ActionBus\Contract\RollbackActionInterface;
use Duyler\ActionBus\Dto\Result;

use function is_callable;

final class Rollback
{
    public function __construct(
        private CompleteActionCollection $completeActionCollection,
        private ActionContainerCollection $containerCollection,
        private ActionArgumentCollection $actionArgumentCollection,
    ) {}

    public function run(array $slice = []): void
    {
        $completeActions = empty($slice)
            ? $this->completeActionCollection->getAll()
            : $this->completeActionCollection->getAllByArray($slice);

        foreach ($completeActions as $completeAction) {
            if (null === $completeAction->action->rollback) {
                continue;
            }

            $argument = $this->actionArgumentCollection->isExists($completeAction->action->id)
                ? $this->actionArgumentCollection->get($completeAction->action->id)
                : null;

            if (is_callable($completeAction->action->rollback)) {
                ($completeAction->action->rollback)($completeAction->result, $argument);
                continue;
            }

            $actionContainer = $this->containerCollection->get($completeAction->action->id);

            /** @var RollbackActionInterface $rollback */
            $rollback = $actionContainer->get($completeAction->action->rollback);
            $this->rollback($rollback, $completeAction->result, $argument);
        }
    }

    private function rollback(RollbackActionInterface $rollback, Result $result, object|null $argument): void
    {
        $rollback->run($result, $argument);
    }
}
