<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Action\Exception\ActionHandlerMustBeCallableException;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Bus\ActionContainer;

class ActionHandlerBuilder
{
    public function __construct(
        private ActionSubstitution $actionSubstitution,
    ) {}

    public function build(Action $action, ActionContainer $container): Closure
    {
        if ($this->actionSubstitution->isSubstituteHandler($action->id)) {
            $handlerSubstitution = $this->actionSubstitution->getSubstituteHandler($action->id);
            if ($handlerSubstitution->handler instanceof Closure) {
                return $handlerSubstitution->handler;
            }
            $container->addProviders($handlerSubstitution->providers);
            $container->bind($handlerSubstitution->bind);
            return $this->getCallableHandler($container, $handlerSubstitution->handler);
        }

        if ($action->handler instanceof Closure) {
            return $action->handler;
        }

        return $this->getCallableHandler($container, $action->handler);
    }

    private function getCallableHandler(ActionContainer $container, string $handler): Closure
    {
        $resolvedHandler = $container->get($handler);
        if (!is_callable($resolvedHandler)) {
            throw new ActionHandlerMustBeCallableException();
        }
        return $resolvedHandler(...);
    }
}
