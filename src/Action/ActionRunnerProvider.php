<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Closure;
use Duyler\EventBus\Action\Exception\ActionHandlerMustBeCallableException;
use Duyler\EventBus\Contract\ActionRunnerProviderInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Internal\Event\ActionAfterRunEvent;
use Duyler\EventBus\Internal\Event\ActionBeforeRunEvent;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;
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

        if (!is_callable($handler)) {
            throw new ActionHandlerMustBeCallableException();
        }

        return function () use ($action, $handler, $argument): mixed {
            $this->eventDispatcher->dispatch(new ActionBeforeRunEvent($action));

            try {
                $resultData = $handler($argument);
            } catch (Throwable $exception) {
                $this->eventDispatcher->dispatch(new ActionThrownExceptionEvent($action, $exception));
                throw $exception;
            }

            $this->eventDispatcher->dispatch(new ActionAfterRunEvent($action));
            return $resultData;
        };
    }
}
