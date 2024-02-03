<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Action\ActionContainerBind;
use Duyler\EventBus\Internal\Event\ActionIsCompleteEvent;

class BindContractCompleteActionEventListener
{
    public function __construct(private ActionContainerBind $actionContainerBind) {}

    public function __invoke(ActionIsCompleteEvent $event)
    {
        $this->actionContainerBind->add($event->completeAction->action, $event->completeAction->result);
    }
}
