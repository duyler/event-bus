<?php

declare(strict_types=1);

namespace Duyler\EventBus\Actor;

use Duyler\EventBus\Bus\Actor;
use Duyler\EventBus\Contract\ActorRunnerInterface;
use Duyler\EventBus\Contract\ActorRunnerProviderInterface;
use Duyler\EventBus\Internal\Event\ActorAfterRunEvent;
use Duyler\EventBus\Internal\Event\ActorBeforeRunEvent;
use Duyler\EventBus\Internal\Event\ActorThrownExceptionEvent;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ActorRunnerProvider implements ActorRunnerProviderInterface
{
    public function __construct(
        private readonly ActorContainerProvider $actorContainerProvider,
        private readonly ActorHandlerArgumentBuilder $argumentBuilder,
        private readonly ActorHandlerBuilder $handlerBuilder,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function getRunner(Actor $actor, string $scope): ActorRunnerInterface
    {
        $container = $this->actorContainerProvider->get($actor, $scope);
        $handler = $this->handlerBuilder->build($actor, $container);
        $argument = $this->argumentBuilder->build($actor, $container, $scope);

        $runner = function () use ($actor, $handler, $argument): mixed {
            $this->eventDispatcher->dispatch(new ActorBeforeRunEvent($actor, $argument));

            try {
                $resultData = $handler($argument);
            } catch (Throwable $exception) {
                $this->eventDispatcher->dispatch(new ActorThrownExceptionEvent($actor, $exception));
                throw $exception;
            }

            $this->eventDispatcher->dispatch(new ActorAfterRunEvent($actor, $resultData));

            return $resultData;
        };

        return new ActorRunner($runner, $argument);
    }
}
