<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\DependencyInjection\ContainerBuilder;
use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Config;
use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateSuspendHandlerInterface;
use Duyler\EventBus\Dto\State\StateAfterHandler;
use Duyler\EventBus\Dto\State\StateBeforeHandler;
use Duyler\EventBus\Dto\State\StateFinalHandler;
use Duyler\EventBus\Dto\State\StateStartHandler;
use Duyler\EventBus\Dto\State\StateSuspendHandler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StateHandlerContainer
{
    /**
     * @var StateStartHandler[]
     */
    private array $startHandlers = [];

    /**
     * @var StateStartHandler[]
     */
    private array $beforeHandlers = [];

    /**
     * @var StateSuspendHandler[]
     */
    private array $suspendHandlers = [];

    /**
     * @var StateAfterHandler[]
     */
    private array $afterHandlers = [];

    /**
     * @var StateFinalHandler[]
     */
    private array $finalHandlers = [];

    private ContainerInterface $container;

    public function __construct(Config $config)
    {
        $this->container = ContainerBuilder::build(new \Duyler\DependencyInjection\Config(
            cacheDirPath: $config->stateHandlerContainerCacheDir,
        ));
    }

    /**
     * @return StateStartHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStartHandlers(): array
    {
        return $this->make($this->startHandlers);
    }

    /**
     * @return StateBeforeHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBeforeHandlers(): array
    {
        return $this->make($this->beforeHandlers);
    }

    /**
     * @return StateSuspendHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getSuspendHandlers(): array
    {
        return $this->make($this->suspendHandlers);
    }

    /**
     * @return StateAfterHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAfterHandlers(): array
    {
        return $this->make($this->afterHandlers);
    }

    /**
     * @return StateFinalHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFinalHandlers(): array
    {
        return $this->make($this->finalHandlers);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function make(array $handlersData): array
    {
        $handlers = [];

        foreach ($handlersData as $handlerData) {
            if ($this->container->has($handlerData->class) === false) {
                $this->container->setProviders($handlerData->providers);
                $this->container->bind($handlerData->classMap);
                $this->container->make($handlerData->class);
            }

            $handlers[] = $this->container->get($handlerData->class);
        }

        return $handlers;
    }

    public function registerStartHandler(StateStartHandler $startHandler): void
    {
        $this->startHandlers[] = $startHandler;
    }

    public function registerBeforeHandler(StateBeforeHandler $beforeHandler): void
    {
        $this->beforeHandlers[] = $beforeHandler;
    }

    public function registerSuspendHandler(StateSuspendHandler $suspendHandler): void
    {
        $this->suspendHandlers[] = $suspendHandler;
    }

    public function registerAfterHandler(StateAfterHandler $afterHandler): void
    {
        $this->afterHandlers[] = $afterHandler;
    }

    public function registerFinalHandler(StateFinalHandler $finalHandler): void
    {
        $this->finalHandlers[] = $finalHandler;
    }
}
