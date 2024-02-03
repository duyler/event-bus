<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Contract\ActionRunnerInterface;
use Duyler\EventBus\Contract\ActionRunnerProviderInterface;
use Duyler\EventBus\Dto\Action;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;

class ActionRunnerProvider implements ActionRunnerProviderInterface
{
    public function __construct(
        private ActionContainerProvider $actionContainerProvider,
        private ActionHandlerArgumentBuilder $argumentBuilder,
        private ActionHandlerBuilder $handlerBuilder,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function getRunner(Action $action): ActionRunnerInterface
    {
        $container = $this->actionContainerProvider->get($action);

        return new ActionRunner(
            actionHandler: $this->handlerBuilder->build($action, $container),
            argument: $this->argumentBuilder->build($action, $container),
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
