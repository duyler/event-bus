<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Dto\StateAfterHandler;
use Duyler\EventBus\Dto\StateBeforeHandler;
use Duyler\EventBus\Dto\StateFinalHandler;
use Duyler\EventBus\Storage;

readonly class StateHandlerBuilder
{
    private ContainerInterface $container;
    public function __construct(private Storage $storage,)
    {
        $this->container = ContainerBuilder::build();
    }
    public function createBefore(StateBeforeHandler $beforeStateHandler): void
    {
        $this->storage->state()->save($this->create($beforeStateHandler));
    }

    public function createAfter(StateAfterHandler $afterStateHandler): void
    {
        $this->storage->state()->save($this->create($afterStateHandler));
    }

    public function createFinal(StateFinalHandler $finalStateHandler): void
    {
        $this->storage->state()->save($this->create($finalStateHandler));
    }

    private function create(
        StateBeforeHandler|StateAfterHandler|StateFinalHandler $handler,
    ): StateAfterHandlerInterface|StateBeforeHandlerInterface|StateFinalHandlerInterface {
        $this->container->setProviders($handler->providers);
        $this->container->bind($handler->classMap);
        return $this->container->make($handler->class);
    }
}
