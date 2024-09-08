<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Validator;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Internal\Event\TaskAfterRunEvent;

class ValidateCompleteActionEventListener
{
    public function __construct(
        private Validator $validateService,
        private CompleteActionStorage $completeActionStorage,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = $this->completeActionStorage->get($event->task->action->id);
        $this->validateService->validateCompleteAction($completeAction);
    }
}
