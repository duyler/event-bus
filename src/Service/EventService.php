<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Build\Event;
use Duyler\ActionBus\Bus\Bus;
use Duyler\ActionBus\Bus\EventRelation;
use Duyler\ActionBus\Exception\DispatchedEventNotDefinedException;
use Duyler\ActionBus\Internal\Event\EventRemovedEvent;
use Duyler\ActionBus\Storage\ActionStorage;
use Duyler\ActionBus\Storage\EventRelationStorage;
use Duyler\ActionBus\Dto\Event as EventDto;
use Duyler\ActionBus\Exception\ContractForDataNotReceivedException;
use Duyler\ActionBus\Exception\DataForContractNotReceivedException;
use Duyler\ActionBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\ActionBus\Exception\EventHandlersNotFoundException;
use Duyler\ActionBus\Storage\EventStorage;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventService
{
    public function __construct(
        private EventRelationStorage $eventRelationStorage,
        private ActionStorage $actionStorage,
        private EventStorage $eventStorage,
        private EventDispatcherInterface $eventDispatcher,
        private Bus $bus,
    ) {}

    public function dispatch(EventDto $eventDto): void
    {
        $event = $this->eventStorage->get($eventDto->id);

        if (null === $event) {
            throw new DispatchedEventNotDefinedException($eventDto->id);
        }

        if (null !== $eventDto->data) {
            if (null === $event->contract) {
                throw new ContractForDataNotReceivedException($eventDto->id);
            }

            if (false === $eventDto->data instanceof $event->contract) {
                throw new DataMustBeCompatibleWithContractException($eventDto->id, $event->contract);
            }
        } else {
            if (null !== $event->contract) {
                throw new DataForContractNotReceivedException($eventDto->id, $event->contract);
            }
        }

        $actions = $this->actionStorage->getByEvent($eventDto->id);

        if (count($actions) === 0) {
            throw new EventHandlersNotFoundException($eventDto->id);
        }

        foreach ($actions as $action) {
            $this->eventRelationStorage->save(new EventRelation($action, $eventDto));
            $this->bus->doAction($action);
        }
    }

    /**
     * @param array<string, Event> $events
     */
    public function collect(array $events): void
    {
        foreach ($events as $event) {
            $this->eventStorage->save($event);
        }
    }

    public function addEvent(Event $event): void
    {
        $this->eventStorage->save($event);
    }

    public function removeEvent(string $eventId): void
    {
        $this->eventStorage->remove($eventId);
        $this->eventDispatcher->dispatch(
            new EventRemovedEvent($eventId),
        );
    }
}
