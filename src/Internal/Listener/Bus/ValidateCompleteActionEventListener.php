<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\Validator;
use Duyler\EventBus\Internal\Event\ActionIsCompleteEvent;

class ValidateCompleteActionEventListener
{
    public function __construct(private Validator $validateService) {}

    public function __invoke(ActionIsCompleteEvent $event)
    {
        $this->validateService->validateCompleteAction($event->completeAction);
    }
}
