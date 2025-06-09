<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Action\Exception\ActionHandlerMustBeCallableException;
use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Bus\ActionContainer;

class ActionHandlerBuilder
{
    public function __construct(
        private readonly ActionSubstitution $actionSubstitution,
    ) {}

    public function build(Action $action, ActionContainer $container): Closure
    {
        if ($this->actionSubstitution->isSubstituteHandler($action->getId())) {
            $handlerSubstitution = $this->actionSubstitution->getSubstituteHandler($action->getId());
            if ($handlerSubstitution->handler instanceof Closure) {
                return $handlerSubstitution->handler;
            }
            $container->addProviders($handlerSubstitution->providers);
            $container->bind($handlerSubstitution->bind);
            return $this->getCallableHandler($container, $handlerSubstitution->handler);
        }

        if ($action->getHandler() instanceof Closure) {
            return $action->getHandler();
        }

        return $this->getCallableHandler($container, $action->getHandler());
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
