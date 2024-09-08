<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DependencyInjection\Attribute\Finalize;
use Duyler\EventBus\Bus\EventRelation;
use RuntimeException;

#[Finalize(method: 'reset')]
class EventRelationStorage
{
    /**
     * @var array<string, array<array-key, EventRelation>>
     */
    private array $data = [];

    /** @var array<string, EventRelation> */
    private array $lastById = [];

    public function save(EventRelation $eventRelation): void
    {
        $this->data[$eventRelation->action->id][] = $eventRelation;
        $this->lastById[$eventRelation->event->id] = $eventRelation;
    }

    public function has(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }

    public function shift(string $actionId): EventRelation
    {
        $this->data[$actionId] ?? throw new RuntimeException('Event relation for action ' . $actionId . ' not found');

        return array_shift($this->data[$actionId]);
    }

    public function getLast(string $eventId): EventRelation
    {
        return $this->lastById[$eventId];
    }

    public function isExists(string $eventId): bool
    {
        return isset($this->lastById[$eventId]);
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function reset(): void
    {
        $this->data = [];
        $this->lastById = [];
    }
}
