<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Doctrine\Common\Collections\ArrayCollection;
use Duyler\EventBus\Enum\StateType;

readonly class StateHandlerProvider
{
    public function __construct(private StateHandlerContainer $container)
    {
    }

    public function getHandlers(StateType $stateType): ArrayCollection
    {
        return $this->container->get($stateType);
    }
}
