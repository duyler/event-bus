<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Validator;
use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;

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
