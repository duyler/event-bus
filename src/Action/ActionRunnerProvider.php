<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Action;

use Closure;
use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Contract\ActionRunnerProviderInterface;
use Duyler\ActionBus\Internal\Event\ActionAfterRunEvent;
use Duyler\ActionBus\Internal\Event\ActionBeforeRunEvent;
use Duyler\ActionBus\Internal\Event\ActionThrownExceptionEvent;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ActionRunnerProvider implements ActionRunnerProviderInterface
{
    public function __construct(
        private ActionContainerProvider $actionContainerProvider,
        private ActionHandlerArgumentBuilder $argumentBuilder,
        private ActionHandlerBuilder $handlerBuilder,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function getRunner(Action $action): Closure
    {
        $container = $this->actionContainerProvider->get($action);
        $handler = $this->handlerBuilder->build($action, $container);
        $argument = $this->argumentBuilder->build($action, $container);

        return function () use ($action, $handler, $argument): mixed {
            $this->eventDispatcher->dispatch(new ActionBeforeRunEvent($action, $argument));

            try {
                $resultData = $handler($argument);
            } catch (Throwable $exception) {
                $this->eventDispatcher->dispatch(new ActionThrownExceptionEvent($action, $exception));
                throw $exception;
            }

            $this->eventDispatcher->dispatch(new ActionAfterRunEvent($action, $resultData));

            return $resultData;
        };
    }
}
