<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Service\ActionService;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;

final readonly class CleanByLimitEventListener
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private BusConfig $busConfig,
        private ActionService $actionService,
        private EventRelationStorage $eventRelationStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if (0 === $this->busConfig->maxCountCompleteActions) {
            return;
        }

        $completeActions = $this->completeActionStorage->getAll();

        if (count($completeActions) > $this->busConfig->maxCountCompleteActions) {
            /** @var string $firstCompleteActionId */
            $firstCompleteActionId = array_key_first($completeActions);
            $firstCompleteAction = $completeActions[$firstCompleteActionId];
            $this->actionService->removeAction($firstCompleteAction->action->id);
            $this->completeActionStorage->remove($firstCompleteAction->action->id);
            $this->eventRelationStorage->removeByActionId($firstCompleteAction->action->id);
        }
    }
}
