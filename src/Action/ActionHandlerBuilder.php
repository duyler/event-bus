<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Dto\Action;

class ActionHandlerBuilder
{
    public function __construct(
        private ActionSubstitution $actionSubstitution,
    ) {}

    public function build(Action $action, ActionContainer $container): callable
    {
        if ($this->actionSubstitution->isSubstituteHandler($action->id)) {
            return $container->get($this->actionSubstitution->getSubstituteHandler($action->id));
        }

        if (is_callable($action->handler)) {
            return $action->handler;
        }

        return $container->get($action->handler);
    }
}
