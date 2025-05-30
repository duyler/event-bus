<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Contract\ActionRunnerProviderInterface;
use Duyler\EventBus\Internal\Event\ActionAfterRunEvent;
use Duyler\EventBus\Internal\Event\ActionBeforeRunEvent;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ActionRunnerProvider implements ActionRunnerProviderInterface
{
    public function __construct(
        private readonly ActionContainerProvider $actionContainerProvider,
        private readonly ActionHandlerArgumentBuilder $argumentBuilder,
        private readonly ActionHandlerBuilder $handlerBuilder,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function getRunner(Action $action): ActionRunnerInterface
    {
        $container = $this->actionContainerProvider->get($action);
        $handler = $this->handlerBuilder->build($action, $container);
        $argument = $this->argumentBuilder->build($action, $container);

        $runner = function () use ($action, $handler, $argument): mixed {
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

        return new ActionRunner($runner, $argument);
    }
}
