<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Dto\Result;

use function is_callable;

class Rollback
{
    public function __construct(
        private CompleteActionCollection $completeActionCollection,
        private ActionContainerCollection $containerCollection,
    ) {}

    public function run(array $slice = []): void
    {
        $completeActions = empty($slice)
            ? $this->completeActionCollection->getAll()
            : $this->completeActionCollection->getAllByArray($slice);

        foreach ($completeActions as $completeAction) {
            if (empty($completeAction->action->rollback)) {
                continue;
            }

            if (is_callable($completeAction->action->rollback)) {
                ($completeAction->action->rollback)();
                continue;
            }

            $actionContainer = $this->containerCollection->get($completeAction->action->id);

            $this->rollback($actionContainer->get($completeAction->action->rollback), $completeAction->result);
        }
    }

    private function rollback(RollbackActionInterface $rollback, Result $result): void
    {
        $rollback->run($result);
    }
}
