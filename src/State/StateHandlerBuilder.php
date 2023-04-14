<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateStartHandlerInterface;
use Duyler\EventBus\Dto\StateAfterHandler;
use Duyler\EventBus\Dto\StateBeforeHandler;
use Duyler\EventBus\Dto\StateFinalHandler;
use Duyler\EventBus\Dto\StateStartHandler;
use Duyler\EventBus\Storage;

readonly class StateHandlerBuilder
{
    private ContainerInterface $container;
    public function __construct(private Storage $storage,)
    {
        $this->container = ContainerBuilder::build();
    }
    public function createStart(StateStartHandler $stateStartHandler): void
    {
        $this->storage->state()->save($this->create($stateStartHandler));
    }

    public function createBefore(StateBeforeHandler $stateBeforeHandler): void
    {
        $this->storage->state()->save($this->create($stateBeforeHandler));
    }

    public function createAfter(StateAfterHandler $stateAfterHandler): void
    {
        $this->storage->state()->save($this->create($stateAfterHandler));
    }

    public function createFinal(StateFinalHandler $stateFinalHandler): void
    {
        $this->storage->state()->save($this->create($stateFinalHandler));
    }

    private function create(
        StateStartHandler|StateBeforeHandler|StateAfterHandler|StateFinalHandler $handler,
    ): StateStartHandlerInterface|StateAfterHandlerInterface|StateBeforeHandlerInterface|StateFinalHandlerInterface {
        $this->container->setProviders($handler->providers);
        $this->container->bind($handler->classMap);
        return $this->container->make($handler->class);
    }
}