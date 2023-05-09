<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Contract\State\StateAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateFinalHandlerInterface;
use Duyler\EventBus\Contract\State\StateStartHandlerInterface;
use Duyler\EventBus\Contract\State\StateSuspendHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

readonly class StateHandlerProvider
{
    public function __construct(private StateHandlerContainer $container)
    {
    }

    /**
     * @return StateStartHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStartHandlers(): array
    {
        return $this->container->getStartHandlers();
    }

    /**
     * @return StateBeforeHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBeforeHandlers(): array
    {
        return $this->container->getBeforeHandlers();
    }

    /**
     * @return StateSuspendHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getSuspendHandlers(): array
    {
        return $this->container->getSuspendHandlers();
    }

    /**
     * @return StateAfterHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAfterHandlers(): array
    {
        return $this->container->getAfterHandlers();
    }

    /**
     * @return StateFinalHandlerInterface[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFinalHandlers(): array
    {
        return $this->container->getFinalHandlers();
    }
}
