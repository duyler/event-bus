<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Doctrine\Common\Collections\ArrayCollection;
use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Config;
use Duyler\EventBus\Dto\StateHandler;
use Duyler\EventBus\Enum\StateType;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StateHandlerContainer
{
    private ContainerInterface $container;
    private StateHandlerCollection $handlerCollection;

    public function __construct(Config $config, StateHandlerCollection $handlerCollection)
    {
        $this->container = ContainerBuilder::build(new \Duyler\DependencyInjection\Config(
            cacheDirPath: $config->stateHandlerContainerCacheDir,
        ));

        $this->handlerCollection = $handlerCollection;
    }

    public function get(StateType $stateType): ArrayCollection
    {
        return $this->make($this->handlerCollection->where('type', $stateType));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function make(iterable $handlersData): ArrayCollection
    {
        $handlers = new ArrayCollection();

        foreach ($handlersData as $handlerData) {
            if ($this->container->has($handlerData->class) === false) {
                $this->container->setProviders($handlerData->providers);
                $this->container->bind($handlerData->classMap);
                $this->container->make($handlerData->class);
            }

            $handlers->add($this->container->get($handlerData->class));
        }

        return $handlers;
    }

    public function add(StateHandler $stateHandler): void
    {
        $this->handlerCollection->add($stateHandler);
    }
}
