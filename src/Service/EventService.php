<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\EventRelation;
use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Exception\DispatchedEventNotDefinedException;
use Duyler\EventBus\Internal\Event\EventRemovedEvent;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Dto\Event as EventDto;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\EventBus\Storage\EventStorage;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventService
{
    public function __construct(
        private readonly EventRelationStorage $eventRelationStorage,
        private readonly ActionStorage $actionStorage,
        private readonly EventStorage $eventStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Bus $bus,
        private readonly State $state,
    ) {}

    public function dispatch(EventDto $eventDto): void
    {
        $event = $this->eventStorage->get($eventDto->id);

        if (null === $event) {
            throw new DispatchedEventNotDefinedException($eventDto->id);
        }

        if (null !== $eventDto->data) {
            if (null === $event->type) {
                throw new ContractForDataNotReceivedException($eventDto->id);
            }

            if (false === $eventDto->data instanceof $event->type) {
                throw new DataMustBeCompatibleWithContractException($eventDto->id, $event->type);
            }
        } else {
            if (null !== $event->type) {
                throw new DataForContractNotReceivedException($eventDto->id, $event->type);
            }
        }

        $actions = $this->actionStorage->getByEvent($eventDto->id);

        foreach ($actions as $action) {
            $this->eventRelationStorage->save(new EventRelation($action, $eventDto));
            $this->bus->doAction($action);
        }

        if ($this->eventRelationStorage->isExists($eventDto->id)) {
            $this->state->pushEventLog($eventDto->id);
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
        $this->eventStorage->saveDynamic($event);
    }

    public function removeEvent(string $eventId): void
    {
        $this->eventStorage->removeDynamic($eventId);
        $this->eventDispatcher->dispatch(
            new EventRemovedEvent($eventId),
        );
    }
}
