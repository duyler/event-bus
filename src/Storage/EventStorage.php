<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Storage;

use Duyler\ActionBus\Build\Event;

class EventStorage
{
    /** @var array<string, Event> */
    private array $events = [];

    public function save(Event $event): void
    {
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
}
