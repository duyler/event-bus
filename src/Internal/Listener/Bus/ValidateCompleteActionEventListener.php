<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Validator;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;
use Duyler\EventBus\Storage\CompleteActionStorage;

class ValidateCompleteActionEventListener
{
    public function __construct(
        private readonly Validator $validateService,
        private readonly CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        if ($this->completeActionStorage->isExists($event->task->action->getId())) {
            $completeAction = $this->completeActionStorage->get($event->task->action->getId());
            $this->validateService->validateCompleteAction($completeAction);
        }
    }
}
