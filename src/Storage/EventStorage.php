<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\EventBus\Build\Event;

use function array_key_exists;

class EventStorage
{
    /** @var array<string, Event> */
    private array $events = [];

    /** @var array<string, Event> */
    private array $dynamic = [];

    public function save(Event $event): void
    {
        $this->events[$event->id] = $event;
    }

    public function saveDynamic(Event $event): void
    {
        $this->dynamic[$event->id] = $event;
        $this->events[$event->id] = $event;
    }

    public function get(string $id): ?Event
    {
        return $this->events[$id] ?? null;
    }

    public function remove(string $eventId): void
    {
        unset($this->events[$eventId]);
    }

    public function has(string $eventId): bool
    {
        return isset($this->events[$eventId]);
    }

    public function removeDynamic(string $eventId): void
    {
        if (array_key_exists($eventId, $this->dynamic)) {
            unset($this->events[$eventId]);
            unset($this->dynamic[$eventId]);
        }
    }

    public function getAllDynamic(): array
    {
        return $this->dynamic;
    }
}
