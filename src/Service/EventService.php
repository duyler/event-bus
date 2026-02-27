<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Bus\Bus;
use Duyler\EventBus\Bus\EventRelation;
use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Dto\Event as EventDto;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\ContractForDataNotReceivedException;
use Duyler\EventBus\Exception\DataForContractNotReceivedException;
use Duyler\EventBus\Exception\DataMustBeCompatibleWithContractException;
use Duyler\EventBus\Exception\DispatchedEventNotDefinedException;
use Duyler\EventBus\Internal\Event\EventAddedEvent;
use Duyler\EventBus\Internal\Event\EventRemovedEvent;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
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

        $this->validateEventData($eventDto->id, $eventDto->data, $event->type);

        $actions = $this->actionStorage->getBySubscriptionEvent($eventDto->id);

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
            $this->eventDispatcher->dispatch(new EventAddedEvent($event));
        }
    }

    public function addEvent(Event $event): void
    {
        $this->eventStorage->saveDynamic($event);
        $this->eventDispatcher->dispatch(new EventAddedEvent($event));
    }

    public function removeEvent(string $eventId): void
    {
        $event = $this->eventStorage->get($eventId);

        $this->eventStorage->removeDynamic($eventId);

        if (null !== $event) {
            $this->eventDispatcher->dispatch(
                new EventRemovedEvent($event),
            );
        }
    }

    public function dispatchActionEvent(string $actionId, Result $result): void
    {
        $eventDto = new EventDto($actionId, $result->status, $result->data);

        $actions = $this->actionStorage->getBySubscriptionEvent($eventDto->id);

        foreach ($actions as $action) {
            $this->eventRelationStorage->save(new EventRelation($action, $eventDto));
            $this->bus->doAction($action);
        }

        $this->state->pushEventLog($eventDto->id);
    }

    private function validateEventData(string $eventId, ?object $data, ?string $type): void
    {
        if (null !== $data) {
            if (null === $type) {
                throw new ContractForDataNotReceivedException($eventId);
            }

            if (false === $data instanceof $type) {
                throw new DataMustBeCompatibleWithContractException($eventId, $type);
            }
        } else {
            if (null !== $type) {
                throw new DataForContractNotReceivedException($eventId, $type);
            }
        }
    }
}
