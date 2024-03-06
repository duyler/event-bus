<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Action\Exception\ActionHandlerMustBeCallableException;
use Duyler\EventBus\Bus\ActionContainer;
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

        if ($action->handler instanceof Closure) {
            return $action->handler;
        }

        $handler = $container->get($action->handler);

        if (!is_callable($handler)) {
            throw new ActionHandlerMustBeCallableException();
        }

        return $handler;
    }
}
