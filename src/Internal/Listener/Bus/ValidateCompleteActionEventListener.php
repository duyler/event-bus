<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\Bus;

use Duyler\ActionBus\Bus\Validator;
use Duyler\ActionBus\Collection\CompleteActionCollection;
use Duyler\ActionBus\Internal\Event\TaskAfterRunEvent;

class ValidateCompleteActionEventListener
{
    public function __construct(
        private Validator $validateService,
        private CompleteActionCollection $completeActionCollection,
    ) {}

    public function __invoke(TaskAfterRunEvent $event): void
    {
        $completeAction = $this->completeActionCollection->get($event->task->action->id);
        $this->validateService->validateCompleteAction($completeAction);
    }
}
