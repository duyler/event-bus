<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor;

use Closure;
use Duyler\EventBus\Actor\Exception\ActorHandlerMustBeCallableException;
use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Bus\ActorContainer;

class ActorHandlerBuilder
{
    public function __construct(
        private readonly ActorSubstitution $actorSubstitution,
    ) {}

    public function build(Actor $actor, ActorContainer $container): Closure
    {
        if ($this->actorSubstitution->isSubstituteHandler($actor->getId())) {
            $handlerSubstitution = $this->actorSubstitution->getSubstituteHandler($actor->getId());
            if ($handlerSubstitution->handler instanceof Closure) {
                return $handlerSubstitution->handler;
            }
            $container->addProviders($handlerSubstitution->providers);
            $container->bind($handlerSubstitution->bind);
            return $this->getCallableHandler($container, $handlerSubstitution->handler);
        }

        if ($actor->getHandler() instanceof Closure) {
            return $actor->getHandler();
        }

        return $this->getCallableHandler($container, $actor->getHandler());
    }

    private function getCallableHandler(ActorContainer $container, string $handler): Closure
    {
        $resolvedHandler = $container->get($handler);
        if (!is_callable($resolvedHandler)) {
            throw new ActorHandlerMustBeCallableException();
        }
        return $resolvedHandler(...);
    }
}
