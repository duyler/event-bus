<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Bus\Bus;
use Duyler\ActionBus\Bus\EventRelation;
use Duyler\ActionBus\Storage\ActionStorage;
use Duyler\ActionBus\Storage\EventRelationStorage;
use Duyler\ActionBus\Dto\Event;
use Duyler\ActionBus\Exception\ContractForDataNotReceivedException;
use Duyler\ActionBus\Exception\DataForContractNotReceivedException;
use Duyler\ActionBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\ActionBus\Exception\EventHandlersNotFoundException;

class EventService
{
    public function __construct(
        private EventRelationStorage $eventRelationStorage,
        private ActionStorage $actionStorage,
        private Bus $bus,
    ) {}

    public function dispatch(Event $event): void
    {
        if (null !== $event->data) {
            if (null === $event->contract) {
                throw new ContractForDataNotReceivedException($event->id);
            }

            if (false === $event->data instanceof $event->contract) {
                throw new DataMustBeCompatibleWithContractException($event->id, $event->contract);
            }
        } else {
            if (null !== $event->contract) {
                throw new DataForContractNotReceivedException($event->id, $event->contract);
            }
        }

        $actions = $this->actionStorage->getByEvent($event->id);

        if (count($actions) === 0) {
            throw new EventHandlersNotFoundException($event->id);
        }

        foreach ($actions as $action) {
            $this->eventRelationStorage->save(new EventRelation($action, $event));
            $this->bus->doAction($action);
        }
    }
}
