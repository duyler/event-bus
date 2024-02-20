<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Dto\Action;

class ActionHandlerBuilder
{
    public function __construct(
        private ActionSubstitution $actionSubstitution,
    ) {}

    public function build(Action $action, ActionContainer $container): object
    {
        if ($this->actionSubstitution->isSubstituteHandler($action->id)) {
            return $container->get($this->actionSubstitution->getSubstituteHandler($action->id));
        }

        if ($action->handler instanceof Closure) {
            return $action->handler;
        }

        return $container->get($action->handler);
    }
}
