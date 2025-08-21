<?php

declare(strict_types=1);

namespace Duyler\EventBus\Storage;

use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Bus\EventRelation;
use RuntimeException;

use function array_shift;

#[Finalize(method: 'reset')]
class EventRelationStorage
{
    /**
     * @var array<string, array<string, array<array-key, EventRelation>>>
     */
    private array $data = [];

    /** @var array<string, EventRelation> */
    private array $lastById = [];

    public function save(EventRelation $eventRelation): void
    {
        $this->data[$eventRelation->action->getId()][$eventRelation->event->id][] = $eventRelation;
        $this->lastById[$eventRelation->event->id] = $eventRelation;
    }

    public function has(string $actionId): bool
    {
        return isset($this->data[$actionId]);
    }

    public function shift(string $actionId, string $eventId): EventRelation
    {
        $this->data[$actionId][$eventId] ?? throw new RuntimeException('Event relation for action ' . $actionId . ' not found');

        /** @var EventRelation $eventRelation */
        $eventRelation = array_shift($this->data[$actionId][$eventId]);

        return $eventRelation;
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

    // @toto Need refactor to remove from lastById without foreach
    public function removeByActionId(string $actionId): void
    {
        if (isset($this->data[$actionId])) {
            unset($this->data[$actionId]);
        }

        foreach ($this->lastById as $relation) {
            if ($relation->action->getId() === $actionId) {
                unset($this->lastById[$relation->event->id]);
            }
        }
    }
}
