<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Doctrine\Common\Collections\ArrayCollection;
use Duyler\EventBus\Enum\StateType;

readonly class StateHandlerProvider
{
    public function __construct(private StateHandlerCollection $handlerCollection)
    {
    }

    public function getHandlers(StateType $stateType): ArrayCollection
    {
        $handlers = new ArrayCollection();

        foreach ($this->handlerCollection->where('type', $stateType) as $handlerDto) {
            $handlers->add($handlerDto->handler);
        }

        return $handlers;
    }
}
